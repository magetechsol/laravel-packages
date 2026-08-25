<?php

declare(strict_types=1);

namespace MageTech\Workflow\Tests\Integration;

use MageTech\Workflow\Definition\WorkflowDefinition;
use MageTech\Workflow\Engine\WorkflowManager;
use MageTech\Workflow\Engine\WorkflowRegistrar;
use MageTech\Workflow\Enums\ApprovalType;
use MageTech\Workflow\Tests\Fixtures\handlers\PaymentHandler;
use MageTech\Workflow\Tests\Fixtures\handlers\ShippingHandler;
use MageTech\Workflow\Tests\Fixtures\Order;
use MageTech\Workflow\Tests\TestCase;

class ApprovalWorkflowFlowTest extends TestCase
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

    public function test_single_approval_full_flow(): void
    {
        $definition = WorkflowDefinition::define('single-approval-flow')
            ->step('payment', PaymentHandler::class)
            ->approval('manager', ApprovalType::Single)
                ->approvers([1])
                ->timeout(86400)
            ->step('shipping', ShippingHandler::class)
            ->complete();

        $this->registrar->register($definition);

        $order = Order::create(['total' => 10000.00, 'status' => 'pending']);
        $instance = $this->manager->start('single-approval-flow', $order, startedBy: 1);

        $approvals = $instance->fresh()->approvals;
        $this->assertCount(1, $approvals);
        $this->assertSame('pending', $approvals->first()->status->value);

        $instance = $this->manager->approve($instance->id, 'manager', 1, 'Approved');
        $this->assertNotNull($instance);
    }

    public function test_all_approvers_must_approve(): void
    {
        $definition = WorkflowDefinition::define('all-approval-flow')
            ->step('payment', PaymentHandler::class)
            ->approval('board', ApprovalType::AllApprovers)
                ->approvers([1, 2, 3])
                ->timeout(86400)
            ->step('shipping', ShippingHandler::class)
            ->complete();

        $this->registrar->register($definition);

        $order = Order::create(['total' => 100000.00, 'status' => 'pending']);
        $instance = $this->manager->start('all-approval-flow', $order, startedBy: 1);

        $approvals = $instance->fresh()->approvals;
        $this->assertCount(3, $approvals);

        $instance = $this->manager->approve($instance->id, 'board', 1);
        $instance = $this->manager->approve($instance->id, 'board', 2);
        $instance = $this->manager->approve($instance->id, 'board', 3);

        $this->assertNotNull($instance);
    }

    public function test_rejection_stops_workflow(): void
    {
        $definition = WorkflowDefinition::define('reject-flow')
            ->step('payment', PaymentHandler::class)
            ->approval('manager', ApprovalType::Single)
                ->approvers([1])
            ->step('shipping', ShippingHandler::class)
            ->complete();

        $this->registrar->register($definition);

        $order = Order::create(['total' => 5000.00, 'status' => 'pending']);
        $instance = $this->manager->start('reject-flow', $order, startedBy: 1);

        $instance = $this->manager->reject($instance->id, 'manager', 1, 'Denied');

        $failedStep = $instance->fresh()->steps()->where('name', 'manager')->first();
        $this->assertSame('failed', $failedStep->status->value);
    }
}
