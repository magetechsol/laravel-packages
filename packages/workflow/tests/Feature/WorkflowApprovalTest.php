<?php

declare(strict_types=1);

namespace MageTech\Workflow\Tests\Feature;

use MageTech\Workflow\Definition\WorkflowDefinition;
use MageTech\Workflow\Engine\WorkflowManager;
use MageTech\Workflow\Engine\WorkflowRegistrar;
use MageTech\Workflow\Enums\ApprovalType;
use MageTech\Workflow\Enums\WorkflowStatus;
use MageTech\Workflow\Tests\Fixtures\handlers\InventoryHandler;
use MageTech\Workflow\Tests\Fixtures\handlers\PaymentHandler;
use MageTech\Workflow\Tests\Fixtures\handlers\ShippingHandler;
use MageTech\Workflow\Tests\Fixtures\Order;
use MageTech\Workflow\Tests\TestCase;

class WorkflowApprovalTest extends TestCase
{
    private WorkflowRegistrar $registrar;
    private WorkflowManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        Order::createTable();

        $this->registrar = app(WorkflowRegistrar::class);
        $this->manager = app(WorkflowManager::class);

        $definition = WorkflowDefinition::define('approval-workflow')
            ->step('payment', PaymentHandler::class)
            ->approval('manager', ApprovalType::Single)
                ->approvers([1])
                ->timeout(86400)
            ->step('shipping', ShippingHandler::class)
            ->complete();

        $this->registrar->register($definition);
    }

    protected function tearDown(): void
    {
        Order::dropTable();
        parent::tearDown();
    }

    public function test_approval_step_creates_approval_record(): void
    {
        $order = Order::create(['total' => 1000.00, 'status' => 'pending']);
        $instance = $this->manager->start('approval-workflow', $order);

        $approvals = $instance->fresh()->approvals;
        $this->assertGreaterThan(0, $approvals->count());
    }

    public function test_can_approve_step(): void
    {
        $order = Order::create(['total' => 1000.00, 'status' => 'pending']);
        $instance = $this->manager->start('approval-workflow', $order);

        $instance = $this->manager->approve($instance->id, 'manager', 1, 'Approved');

        $this->assertNotNull($instance);
    }

    public function test_can_reject_step(): void
    {
        $order = Order::create(['total' => 1000.00, 'status' => 'pending']);
        $instance = $this->manager->start('approval-workflow', $order);

        $instance = $this->manager->reject($instance->id, 'manager', 1, 'Not approved');

        $this->assertNotNull($instance);
    }

    public function test_multiple_approval_workflow(): void
    {
        $multiDefinition = WorkflowDefinition::define('multi-approval')
            ->step('payment', PaymentHandler::class)
            ->approval('board', ApprovalType::AllApprovers)
                ->approvers([1, 2, 3])
                ->timeout(86400)
            ->step('shipping', ShippingHandler::class)
            ->complete();

        $this->registrar->register($multiDefinition);

        $order = Order::create(['total' => 50000.00, 'status' => 'pending']);
        $instance = $this->manager->start('multi-approval', $order);

        $approvals = $instance->fresh()->approvals;
        $this->assertCount(3, $approvals);
    }
}
