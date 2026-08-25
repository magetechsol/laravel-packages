<?php

declare(strict_types=1);

namespace MageTech\Workflow\Tests\Feature;

use MageTech\Workflow\Definition\WorkflowDefinition;
use MageTech\Workflow\Engine\WorkflowManager;
use MageTech\Workflow\Engine\WorkflowRegistrar;
use MageTech\Workflow\Enums\WorkflowStatus;
use MageTech\Workflow\Tests\Fixtures\handlers\InventoryHandler;
use MageTech\Workflow\Tests\Fixtures\handlers\NotificationHandler;
use MageTech\Workflow\Tests\Fixtures\handlers\PaymentHandler;
use MageTech\Workflow\Tests\Fixtures\handlers\ShippingHandler;
use MageTech\Workflow\Tests\Fixtures\Order;
use MageTech\Workflow\Tests\TestCase;

class WorkflowExecutionTest extends TestCase
{
    private WorkflowRegistrar $registrar;
    private WorkflowManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        Order::createTable();

        $this->registrar = app(WorkflowRegistrar::class);
        $this->manager = app(WorkflowManager::class);

        $definition = WorkflowDefinition::define('order-processing')
            ->step('payment', PaymentHandler::class)
            ->step('inventory', InventoryHandler::class)
            ->step('shipping', ShippingHandler::class)
            ->step('notify', NotificationHandler::class)
            ->complete();

        $this->registrar->register($definition);
    }

    protected function tearDown(): void
    {
        Order::dropTable();
        parent::tearDown();
    }

    public function test_can_start_workflow(): void
    {
        $order = Order::create(['total' => 150.00, 'status' => 'pending']);

        $instance = $this->manager->start('order-processing', $order);

        $this->assertNotNull($instance);
        $this->assertNotNull($instance->id);
    }

    public function test_workflow_completes_all_steps(): void
    {
        $order = Order::create(['total' => 250.00, 'status' => 'pending']);

        $instance = $this->manager->start('order-processing', $order);

        $this->assertTrue($instance->fresh()->status->isTerminal());
    }

    public function test_workflow_sets_context_data(): void
    {
        $order = Order::create(['total' => 500.00, 'status' => 'pending']);

        $instance = $this->manager->start('order-processing', $order);

        $context = $instance->fresh()->context;
        $this->assertNotNull($context);
    }

    public function test_workflow_creates_log_entries(): void
    {
        $order = Order::create(['total' => 100.00, 'status' => 'pending']);

        $instance = $this->manager->start('order-processing', $order);

        $logs = $instance->fresh()->logs;
        $this->assertGreaterThan(0, $logs->count());
    }

    public function test_workflow_tracks_transitions(): void
    {
        $order = Order::create(['total' => 100.00, 'status' => 'pending']);

        $instance = $this->manager->start('order-processing', $order);

        $transitions = $instance->fresh()->transitions;
        $this->assertGreaterThan(0, $transitions->count());
    }
}
