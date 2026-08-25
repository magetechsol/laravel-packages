# Testing Guide

How to test workflows with the MTS Laravel Workflow Engine.

## Test Setup

```php
use MageTech\Workflow\Tests\TestCase;

class WorkflowTest extends TestCase
{
    // TestCase handles:
    // - SQLite in-memory database
    // - Migration loading
    // - Config setup
}
```

## Testing Workflow Execution

```php
use MageTech\Workflow\Definition\WorkflowDefinition;
use MageTech\Workflow\Support\Facades\Workflow;

it('executes a simple workflow', function () {
    WorkflowDefinition::define('test-flow')
        ->step('process', TestHandler::class)
        ->complete();

    $instance = Workflow::start('test-flow', $model);

    expect($instance->status)->toBe('completed');
});
```

## Testing Approvals

```php
it('requires approval before proceeding', function () {
    WorkflowDefinition::define('approval-test')
        ->step('before', BeforeHandler::class)
        ->approval('review', ApprovalType::Single)
            ->approvers([1])
        ->step('after', AfterHandler::class)
        ->complete();

    $instance = Workflow::start('approval-test', $model);

    // Step should be waiting for approval
    expect($instance->status)->toBe('pending_approval');

    // Approve
    Workflow::approve($instance->id, 'review', 1, 'Approved');

    // Should now complete
    expect($instance->fresh()->status)->toBe('completed');
});
```

## Testing Conditions

```php
it('skips steps when condition not met', function () {
    WorkflowDefinition::define('conditional-test')
        ->step('always', AlwaysHandler::class)
        ->step('conditional', ConditionalHandler::class)
            ->when(fn ($model) => $model->needs_special_processing)
        ->step('after', AfterHandler::class)
        ->complete();

    $model->needs_special_processing = false;
    $instance = Workflow::start('conditional-test', $model);

    // Conditional step should be skipped
    $steps = $instance->steps()->where('name', 'conditional')->first();
    expect($steps->status)->toBe('skipped');
});
```

## Testing Retry

```php
it('retries failed steps', function () {
    WorkflowDefinition::define('retry-test')
        ->step('flaky', FlakyHandler::class)
            ->maxAttempts(3)
            ->retry(3, 'fixed')
        ->complete();

    $instance = Workflow::start('retry-test', $model);

    // If FlakyHandler fails, it should retry up to 3 times
    $steps = $instance->steps()->where('name', 'flaky')->first();
    expect($steps->attempts)->toBeLessThanOrEqual(3);
});
```

## Testing Cancellation

```php
it('can cancel a running workflow', function () {
    WorkflowDefinition::define('cancel-test')
        ->step('long', LongHandler::class)
        ->complete();

    $instance = Workflow::start('cancel-test', $model);

    Workflow::cancel($instance->id, $userId, 'No longer needed');

    expect($instance->fresh()->status)->toBe('cancelled');
});
```

## Testing with Fake Handler

```php
class FakeHandler implements WorkflowHandlerContract
{
    public static array $results = [];

    public function handle(WorkflowInstance $instance, WorkflowStep $step): array
    {
        return static::$results[$step->name] ?? ['success' => true];
    }
}

it('uses fake handler results', function () {
    FakeHandler::$results = [
        'step1' => ['result' => 'custom_value'],
    ];

    // Use FakeHandler in your workflow definition
});
```

## Asserting Events

```php
use MageTech\Workflow\Events\WorkflowStepCompleted;

it('dispatches events on step completion', function () {
    Event::fake([WorkflowStepCompleted::class]);

    WorkflowDefinition::define('event-test')
        ->step('test', TestHandler::class)
        ->complete();

    Workflow::start('event-test', $model);

    Event::assertDispatched(WorkflowStepCompleted::class, function ($event) {
        return $event->step->name === 'test';
    });
});
```

## Asserting Database State

```php
it('creates audit logs', function () {
    WorkflowDefinition::define('audit-test')
        ->step('test', TestHandler::class)
        ->complete();

    $instance = Workflow::start('audit-test', $model);

    $this->assertDatabaseHas('mts_workflow_logs', [
        'workflow_instance_id' => $instance->id,
        'action' => 'started',
    ]);

    $this->assertDatabaseHas('mts_workflow_transitions', [
        'workflow_instance_id' => $instance->id,
        'from_status' => null,
        'to_status' => 'running',
    ]);
});
```
