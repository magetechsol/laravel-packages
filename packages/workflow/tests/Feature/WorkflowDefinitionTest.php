<?php

declare(strict_types=1);

namespace MageTech\Workflow\Tests\Feature;

use MageTech\Workflow\Definition\WorkflowDefinition;
use MageTech\Workflow\Enums\ApprovalType;
use MageTech\Workflow\Enums\RetryBackoff;
use MageTech\Workflow\Tests\Fixtures\handlers\FailingHandler;
use MageTech\Workflow\Tests\Fixtures\handlers\InventoryHandler;
use MageTech\Workflow\Tests\Fixtures\handlers\NotificationHandler;
use MageTech\Workflow\Tests\Fixtures\handlers\PaymentHandler;
use MageTech\Workflow\Tests\Fixtures\handlers\ShippingHandler;
use MageTech\Workflow\Tests\Fixtures\Order;
use MageTech\Workflow\Tests\TestCase;
use MageTech\Workflow\WorkflowServiceProvider;

class WorkflowDefinitionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Order::createTable();
    }

    protected function tearDown(): void
    {
        Order::dropTable();
        parent::tearDown();
    }

    public function test_can_define_simple_workflow(): void
    {
        $definition = WorkflowDefinition::define('simple')
            ->step('step_one', PaymentHandler::class)
            ->complete();

        $this->assertSame('simple', $definition->getName());
        $this->assertCount(2, $definition->getSteps());
    }

    public function test_can_define_workflow_with_multiple_steps(): void
    {
        $definition = WorkflowDefinition::define('order-processing')
            ->step('payment', PaymentHandler::class)
                ->timeout(30)
                ->maxAttempts(3)
            ->step('inventory', InventoryHandler::class)
            ->step('shipping', ShippingHandler::class)
            ->step('notify', NotificationHandler::class)
            ->complete();

        $this->assertSame('order-processing', $definition->getName());
        $this->assertCount(5, $definition->getSteps());
    }

    public function test_can_define_approval_step(): void
    {
        $definition = WorkflowDefinition::define('with-approval')
            ->step('payment', PaymentHandler::class)
            ->approval('manager', ApprovalType::AnyApprover)
                ->approvers([1, 2, 3])
                ->timeout(86400)
            ->complete();

        $this->assertCount(3, $definition->getSteps());

        $approvalStep = $definition->getSteps()[1];
        $this->assertSame('manager', $approvalStep->getName());
        $this->assertSame(ApprovalType::AnyApprover, $approvalStep->getApprovalType());
        $this->assertSame([1, 2, 3], $approvalStep->getApprovers());
    }

    public function test_can_set_step_conditions(): void
    {
        $definition = WorkflowDefinition::define('conditional')
            ->step('payment', PaymentHandler::class)
            ->step('inventory', InventoryHandler::class)
                ->when(fn ($order) => $order->requires_stock_check)
            ->complete();

        $inventoryStep = $definition->getSteps()[1];
        $this->assertNotNull($inventoryStep->getCondition());
    }

    public function test_can_set_global_conditions(): void
    {
        $definition = WorkflowDefinition::define('global-condition')
            ->when(fn ($order) => $order->total > 100)
            ->step('payment', PaymentHandler::class)
            ->complete();

        $this->assertNotNull($definition->getWhenCondition());
    }

    public function test_can_configure_retry_settings(): void
    {
        $definition = WorkflowDefinition::define('retry-config')
            ->step('payment', PaymentHandler::class)
                ->maxAttempts(5)
                ->backoff(RetryBackoff::Linear)
                ->baseDelay(30)
            ->complete();

        $step = $definition->getSteps()[0];
        $this->assertSame(5, $step->getMaxAttempts());
        $this->assertSame(RetryBackoff::Linear, $step->getBackoff());
        $this->assertSame(30, $step->getBaseDelay());
    }

    public function test_validate_throws_for_empty_workflow(): void
    {
        $this->expectException(\MageTech\Workflow\Exceptions\WorkflowDefinitionException::class);
        $this->expectExceptionMessage('Workflow must have at least one step.');

        $definition = new WorkflowDefinition('empty');
        $definition->validate();
    }

    public function test_validate_throws_for_duplicate_steps(): void
    {
        $this->expectException(\MageTech\Workflow\Exceptions\WorkflowDefinitionException::class);
        $this->expectExceptionMessage('already defined');

        $definition = WorkflowDefinition::define('dup')
            ->step('step_one')
            ->step('step_one');

        $definition->validate();
    }

    public function test_to_array_and_from_array(): void
    {
        $definition = WorkflowDefinition::define('serializable')
            ->step('payment', PaymentHandler::class)
            ->complete();

        $array = $definition->toArray();
        $restored = WorkflowDefinition::fromArray($array);

        $this->assertSame('serializable', $restored->getName());
        $this->assertCount(2, $restored->getSteps());
    }
}
