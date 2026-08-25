<?php

declare(strict_types=1);

namespace MageTech\Workflow\Tests\Feature;

use MageTech\Workflow\Definition\WorkflowDefinition;
use MageTech\Workflow\Engine\WorkflowManager;
use MageTech\Workflow\Engine\WorkflowRegistrar;
use MageTech\Workflow\Enums\WorkflowStatus;
use MageTech\Workflow\Tests\Fixtures\handlers\PaymentHandler;
use MageTech\Workflow\Tests\Fixtures\Order;
use MageTech\Workflow\Tests\TestCase;

class WorkflowCancellationTest extends TestCase
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

    public function test_can_cancel_running_workflow(): void
    {
        $definition = WorkflowDefinition::define('cancel-test')
            ->step('payment', PaymentHandler::class)
            ->complete();

        $this->registrar->register($definition);

        $order = Order::create(['total' => 100.00, 'status' => 'pending']);
        $instance = $this->manager->start('cancel-test', $order);

        $cancelled = $this->manager->cancel($instance->id, actorId: 1, reason: 'Changed mind');

        $this->assertSame(WorkflowStatus::Cancelled, $cancelled->status);
    }

    public function test_cancel_marks_steps_cancelled(): void
    {
        $definition = WorkflowDefinition::define('cancel-steps')
            ->step('payment', PaymentHandler::class)
            ->step('next', \MageTech\Workflow\Tests\Fixtures\Handlers\InventoryHandler::class)
            ->complete();

        $this->registrar->register($definition);

        $order = Order::create(['total' => 100.00, 'status' => 'pending']);
        $instance = $this->manager->start('cancel-steps', $order);

        $cancelled = $this->manager->cancel($instance->id);

        $cancelledSteps = $cancelled->fresh()->steps()->where('status', 'cancelled')->count();
        $this->assertGreaterThan(0, $cancelledSteps);
    }

    public function test_cannot_cancel_completed_workflow(): void
    {
        $definition = WorkflowDefinition::define('no-cancel')
            ->step('payment', PaymentHandler::class)
            ->complete();

        $this->registrar->register($definition);

        $order = Order::create(['total' => 100.00, 'status' => 'pending']);
        $instance = $this->manager->start('no-cancel', $order);

        $this->expectException(\MageTech\Workflow\Exceptions\WorkflowDefinitionException::class);
        $this->manager->cancel($instance->id);
    }
}
