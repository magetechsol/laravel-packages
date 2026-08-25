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

class WorkflowConcurrencyTest extends TestCase
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

    public function test_same_workflow_can_be_started_for_different_models(): void
    {
        $definition = WorkflowDefinition::define('concurrent-test')
            ->step('payment', PaymentHandler::class)
            ->complete();

        $this->registrar->register($definition);

        $order1 = Order::create(['total' => 100.00, 'status' => 'pending']);
        $order2 = Order::create(['total' => 200.00, 'status' => 'pending']);

        $instance1 = $this->manager->start('concurrent-test', $order1);
        $instance2 = $this->manager->start('concurrent-test', $order2);

        $this->assertNotSame($instance1->id, $instance2->id);
    }
}
