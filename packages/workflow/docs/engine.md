# Workflow Engine

Deep dive into the MTS Laravel Workflow Engine.

## Architecture

```
WorkflowDefinition (fluent builder)
    ↓
WorkflowManager (registry + resolver)
    ↓
WorkflowRunner (execution engine)
    ├── Step Execution
    │   ├── Sequential steps
    │   ├── Conditional steps
    │   └── Approval steps
    ├── Queue Support
    │   ├── RunWorkflowJob
    │   ├── RunWorkflowStepJob
    │   └── RetryWorkflowStepJob
    └── Audit Logger
        ├── WorkflowLog
        └── WorkflowTransition
```

## Defining Workflows

### Fluent API

```php
use MageTech\Workflow\Definition\WorkflowDefinition;

WorkflowDefinition::define('order-processing')
    ->step('payment', PaymentHandler::class)
        ->timeout(30)
        ->maxAttempts(3)
        ->retry(3, 'exponential')
    ->step('inventory', InventoryHandler::class)
        ->when(fn ($order) => $order->requires_stock_check)
    ->approval('manager', ApprovalType::AnyApprover)
        ->approvers([1, 2, 3])
        ->timeout(86400)
    ->step('shipping', ShippingHandler::class)
    ->step('notify', NotificationHandler::class)
    ->complete();
```

### Step Configuration

```php
->step('name', Handler::class)
    ->timeout(60)           // Seconds before timeout
    ->maxAttempts(3)        // Max retry attempts
    ->retry(3, 'exponential') // Retry count and backoff
    ->queued()              // Run on queue
    ->when(fn ($model) => $condition) // Conditional execution
    ->unless(fn ($model) => $condition) // Skip if true
```

### Conditions

```php
->step('high_value', Handler::class)
    ->when(fn ($order) => $order->total > 100000)

->step('express_skip', Handler::class)
    ->unless(fn ($order) => $order->is_express)
```

## Approval Workflows

```php
use MageTech\Workflow\Enums\ApprovalType;

->approval('manager-approval', ApprovalType::AnyApprover)
    ->approvers([1, 2, 3])
    ->timeout(86400) // 24 hours

->approval('finance', ApprovalType::AllApprovers)
    ->approvers([4, 5])
    ->timeout(172800) // 48 hours
```

### Approval Types

| Type | Behavior |
|------|----------|
| `Single` | One approver required |
| `Multiple` | Each approver decides independently |
| `AnyApprover` | First approval completes the step |
| `AllApprovers` | All must approve |
| `RoleBased` | Anyone with the role can approve |
| `UserBased` | Specific users must approve |

## Step Handlers

```php
use MageTech\Workflow\Contracts\WorkflowHandlerContract;
use MageTech\Workflow\Models\WorkflowInstance;
use MageTech\Workflow\Models\WorkflowStep;

class PaymentHandler implements WorkflowHandlerContract
{
    public function handle(WorkflowInstance $instance, WorkflowStep $step): array
    {
        $order = $instance->workflowable;

        // Process payment...

        return ['payment_verified' => true, 'transaction_id' => 'txn_123'];
    }
}
```

### Return Values

Step return values are stored in the instance context and available to subsequent steps:

```php
class ShippingHandler implements WorkflowHandlerContract
{
    public function handle(WorkflowInstance $instance, WorkflowStep $step): array
    {
        $paymentVerified = $instance->context['payment_verified'] ?? false;

        // Use data from previous steps...

        return ['shipped' => true];
    }
}
```

## Running Workflows

```php
use MageTech\Workflow\Support\Facades\Workflow;

// Start a workflow
$instance = Workflow::start('order-processing', $order, startedBy: $user->id);

// Start with custom context
$instance = Workflow::start('order-processing', $order, [
    'priority' => 'high',
    'source' => 'web',
]);

// Approve a step
Workflow::approve($instance->id, 'manager', $approverId, 'Approved');

// Reject a step
Workflow::reject($instance->id, 'manager', $approverId, 'Rejected');

// Cancel a workflow
Workflow::cancel($instance->id, $userId, 'Customer cancelled');

// Retry a failed workflow
Workflow::retry($instance->id);
```

## Queue Support

```php
->step('heavy_processing', Handler::class)
    ->queued()
    ->timeout(600)
    ->maxAttempts(5)
```

### Queue Configuration

```php
// In config/mts-workflow.php
'queue' => [
    'connection' => 'redis',
    'queue' => 'workflows',
],
```

## Events

Listen to workflow lifecycle events:

```php
use MageTech\Workflow\Events\WorkflowStarted;
use MageTech\Workflow\Events\WorkflowStepCompleted;
use MageTech\Workflow\Events\WorkflowCompleted;

// In EventServiceProvider
protected $listen = [
    WorkflowStarted::class => [LogWorkflowStart::class],
    WorkflowStepCompleted::class => [NotifyStepComplete::class],
    WorkflowCompleted::class => [MarkOrderProcessed::class],
];
```

## Audit Trail

Every action is logged to `mts_workflow_logs` and `mts_workflow_transitions`:

```php
use MageTech\Workflow\Models\WorkflowLog;

$logs = WorkflowLog::where('workflow_instance_id', $instance->id)
    ->orderBy('created_at')
    ->get();

// Each log entry contains:
// - action (started, step_completed, approved, etc.)
// - actor_id (who performed the action)
// - notes (optional message)
// - metadata (JSON context)
```

## Retry Strategy

```php
use MageTech\Workflow\Support\RetryStrategy;

$retry = new RetryStrategy();

// Calculate delay for attempt number
$delay = $retry->calculateDelay(attempt: 2, backoff: 'exponential', baseDelay: 5);
// Returns: 20 seconds (5 * 2^2)

// Supported backoff types:
// - 'fixed': Always returns baseDelay
// - 'linear': baseDelay * attempt
// - 'exponential': baseDelay * 2^(attempt-1)
```

## Concurrency Control

The engine uses database-level locking to prevent concurrent execution of the same workflow instance:

```php
// Automatic locking in WorkflowRunner
// If a workflow is already running, new attempts are rejected
```
