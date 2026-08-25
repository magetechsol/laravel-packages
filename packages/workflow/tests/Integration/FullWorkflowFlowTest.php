<?php

declare(strict_types=1);

namespace MageTech\Workflow\Tests\Integration;

use Illuminate\Support\Facades\Event;
use MageTech\Workflow\Definition\WorkflowDefinition;
use MageTech\Workflow\Engine\WorkflowManager;
use MageTech\Workflow\Engine\WorkflowRegistrar;
use MageTech\Workflow\Enums\ApprovalType;
use MageTech\Workflow\Enums\WorkflowStatus;
use MageTech\Workflow\Events\WorkflowCompleted;
use MageTech\Workflow\Events\WorkflowStarted;
use MageTech\Workflow\Events\WorkflowStepCompleted;
use MageTech\Workflow\Tests\Fixtures\handlers\FailingHandler;
use MageTech\Workflow\Tests\Fixtures\handlers\InventoryHandler;
use MageTech\Workflow\Tests\Fixtures\handlers\NotificationHandler;
use MageTech\Workflow\Tests\Fixtures\handlers\PaymentHandler;
use MageTech\Workflow\Tests\Fixtures\handlers\ShippingHandler;
use MageTech\Workflow\Tests\Fixtures\Order;
use MageTech\Workflow\Tests\TestCase;

class FullWorkflowFlowTest extends TestCase
{
    private WorkflowRegistrar $registrar;
    private WorkflowManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        Order::createTable();

        $this->registrar = app(WorkflowRegistrar::class);
        $this->manager = app(WorkflowManager::class);
    }

    protected function tearDown(): void
    {
        Order::dropTable();
        parent::tearDown();
    }

    public function test_full_successful_workflow_flow(): void
    {
        Event::fake([WorkflowStarted::class, WorkflowStepCompleted::class, WorkflowCompleted::class]);

        $definition = WorkflowDefinition::define('full-order')
            ->step('payment', PaymentHandler::class)
                ->timeout(30)
                ->maxAttempts(3)
            ->step('inventory', InventoryHandler::class)
            ->step('shipping', ShippingHandler::class)
            ->step('notify', NotificationHandler::class)
            ->complete();

        $this->registrar->register($definition);

        $order = Order::create(['total' => 1500.00, 'status' => 'pending']);
        $instance = $this->manager->start('full-order', $order, startedBy: 1);

        $this->assertNotNull($instance);
        $this->assertTrue(in_array($instance->fresh()->status->value, ['completed', 'running']));

        Event::assertDispatched(WorkflowStarted::class);
        Event::assertDispatched(WorkflowStepCompleted::class);
        Event::assertDispatched(WorkflowCompleted::class);

        $logs = $instance->fresh()->logs;
        $this->assertGreaterThan(0, $logs->count());

        $transitions = $instance->fresh()->transitions;
        $this->assertGreaterThan(0, $transitions->count());
    }

    public function test_full_approval_workflow_flow(): void
    {
        $definition = WorkflowDefinition::define('full-approval')
            ->step('payment', PaymentHandler::class)
            ->approval('manager', ApprovalType::Single)
                ->approvers([1])
                ->timeout(86400)
            ->step('shipping', ShippingHandler::class)
            ->step('notify', NotificationHandler::class)
            ->complete();

        $this->registrar->register($definition);

        $order = Order::create(['total' => 50000.00, 'status' => 'pending']);
        $instance = $this->manager->start('full-approval', $order, startedBy: 1);

        $instance = $this->manager->approve($instance->id, 'manager', 1, 'Looks good');

        $this->assertNotNull($instance);
    }

    public function test_workflow_with_condition_and_retry(): void
    {
        $definition = WorkflowDefinition::define('cond-retry')
            ->step('payment', FailingHandler::class)
                ->maxAttempts(1)
            ->step('inventory', InventoryHandler::class)
                ->when(fn ($order) => $order->requires_stock_check)
            ->step('notify', NotificationHandler::class)
            ->complete();

        $this->registrar->register($definition);

        $order = Order::create([
            'total' => 100.00,
            'status' => 'pending',
            'requires_stock_check' => false,
        ]);

        $instance = $this->manager->start('cond-retry', $order);

        $this->assertNotNull($instance);
    }
}
