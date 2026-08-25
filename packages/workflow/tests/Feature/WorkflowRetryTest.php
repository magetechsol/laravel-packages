<?php

declare(strict_types=1);

namespace MageTech\Workflow\Tests\Feature;

use MageTech\Workflow\Definition\WorkflowDefinition;
use MageTech\Workflow\Engine\WorkflowManager;
use MageTech\Workflow\Engine\WorkflowRegistrar;
use MageTech\Workflow\Enums\RetryBackoff;
use MageTech\Workflow\Enums\WorkflowStatus;
use MageTech\Workflow\Tests\Fixtures\handlers\FailingHandler;
use MageTech\Workflow\Tests\Fixtures\handlers\PaymentHandler;
use MageTech\Workflow\Tests\Fixtures\Order;
use MageTech\Workflow\Tests\TestCase;

class WorkflowRetryTest extends TestCase
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

    public function test_failed_workflow_can_be_retried(): void
    {
        $definition = WorkflowDefinition::define('retry-test')
            ->step('payment', FailingHandler::class)
                ->maxAttempts(1)
            ->complete();

        $this->registrar->register($definition);

        $order = Order::create(['total' => 100.00, 'status' => 'pending']);
        $instance = $this->manager->start('retry-test', $order);

        $this->assertTrue($instance->fresh()->status->canRetry());

        $retried = $this->manager->retry($instance->id);
        $this->assertNotNull($retried);
    }

    public function test_retry_resets_step_status(): void
    {
        $definition = WorkflowDefinition::define('retry-reset')
            ->step('payment', FailingHandler::class)
                ->maxAttempts(1)
            ->complete();

        $this->registrar->register($definition);

        $order = Order::create(['total' => 100.00, 'status' => 'pending']);
        $instance = $this->manager->start('retry-reset', $order);

        $failedStep = $instance->fresh()->steps()->where('name', 'payment')->first();
        $this->assertSame('failed', $failedStep->status->value);
        $this->assertSame(1, $failedStep->attempts);

        $this->manager->retry($instance->id);

        $retryStep = $instance->fresh()->steps()->where('name', 'payment')->first();
        $this->assertSame('failed', $retryStep->status->value);
        $this->assertSame(2, $retryStep->attempts);
        $this->assertSame(WorkflowStatus::Failed, $instance->fresh()->status);
    }

    public function test_retry_creates_transition_log(): void
    {
        $definition = WorkflowDefinition::define('retry-log')
            ->step('payment', FailingHandler::class)
                ->maxAttempts(1)
            ->complete();

        $this->registrar->register($definition);

        $order = Order::create(['total' => 100.00, 'status' => 'pending']);
        $instance = $this->manager->start('retry-log', $order);
        $this->manager->retry($instance->id);

        $transitions = $instance->fresh()->transitions;
        $retryTransition = $transitions->where('type.value', 'retried')->first();
        $this->assertNotNull($retryTransition);
    }
}
