<?php

declare(strict_types=1);

namespace MageTech\Workflow\Tests\Feature;

use MageTech\Workflow\Definition\WorkflowDefinition;
use MageTech\Workflow\Engine\WorkflowManager;
use MageTech\Workflow\Engine\WorkflowRegistrar;
use MageTech\Workflow\Tests\Fixtures\handlers\FailingHandler;
use MageTech\Workflow\Tests\Fixtures\handlers\InventoryHandler;
use MageTech\Workflow\Tests\Fixtures\handlers\PaymentHandler;
use MageTech\Workflow\Tests\Fixtures\Order;
use MageTech\Workflow\Tests\TestCase;

class WorkflowConditionTest extends TestCase
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

    public function test_step_condition_met_step_runs(): void
    {
        $definition = WorkflowDefinition::define('cond-met')
            ->step('payment', PaymentHandler::class)
            ->step('inventory', InventoryHandler::class)
                ->when(fn ($order) => $order->requires_stock_check)
            ->complete();

        $this->registrar->register($definition);

        $order = Order::create([
            'total' => 100.00,
            'status' => 'pending',
            'requires_stock_check' => true,
        ]);

        $instance = $this->manager->start('cond-met', $order);
        $this->assertTrue($instance->fresh()->status->isTerminal());
    }

    public function test_step_condition_not_met_step_skipped(): void
    {
        $definition = WorkflowDefinition::define('cond-skip')
            ->step('payment', PaymentHandler::class)
            ->step('inventory', InventoryHandler::class)
                ->when(fn ($order) => $order->requires_stock_check)
            ->complete();

        $this->registrar->register($definition);

        $order = Order::create([
            'total' => 100.00,
            'status' => 'pending',
            'requires_stock_check' => false,
        ]);

        $instance = $this->manager->start('cond-skip', $order);

        $inventoryStep = $instance->fresh()->steps()->where('name', 'inventory')->first();
        $this->assertSame('skipped', $inventoryStep->status->value);
    }

    public function test_global_when_condition_met(): void
    {
        $definition = WorkflowDefinition::define('global-when')
            ->when(fn ($order) => $order->total > 50)
            ->step('payment', PaymentHandler::class)
            ->complete();

        $this->registrar->register($definition);

        $order = Order::create(['total' => 100.00, 'status' => 'pending']);
        $instance = $this->manager->start('global-when', $order);

        $this->assertTrue($instance->fresh()->status->isTerminal());
    }
}
