# MTS Laravel Workflow Engine

Enterprise workflow engine and approval automation for Laravel.

**Package:** `magetech/laravel-workflow`

## Installation

```bash
composer require magetech/laravel-workflow
php artisan mts:workflow:install
php artisan migrate
```

## Requirements

- PHP 8.3+
- Laravel 11.x, 12.x, or 13.x

## Quick Start

### Define a Workflow

```php
use MageTech\Workflow\Definition\WorkflowDefinition;

WorkflowDefinition::define('order-processing')
    ->step('payment', PaymentHandler::class)
        ->timeout(30)
        ->maxAttempts(3)
    ->step('inventory', InventoryHandler::class)
        ->when(fn ($order) => $order->requires_stock_check)
    ->approval('manager', ApprovalType::AnyApprover)
        ->approvers([1, 2, 3])
        ->timeout(86400)
    ->step('shipping', ShippingHandler::class)
    ->step('notify', NotificationHandler::class)
    ->complete();
```

### Start a Workflow

```php
use MageTech\Workflow\Support\Facades\Workflow;

$instance = Workflow::start('order-processing', $order, startedBy: $user->id);
```

### Approve / Reject

```php
Workflow::approve($instance->id, 'manager', $approverId, 'Looks good');
Workflow::reject($instance->id, 'manager', $approverId, 'Denied');
```

### Cancel

```php
Workflow::cancel($instance->id, $userId, 'Order cancelled by customer');
```

## Workflow Types

- **Sequential** - Steps execute in order
- **Approval** - Requires human approval before proceeding
- **Conditional** - Steps execute based on conditions
- **Event-driven** - Workflows triggered by events

## Step Handler

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

        return ['payment_verified' => true];
    }
}
```

## Conditions

```php
->step('high_value_check', HighValueHandler::class)
    ->when(fn ($order) => $order->total > 100000)

->step('skip_if_express', Handler::class)
    ->unless(fn ($order) => $order->is_express)
```

## Approval Types

| Type | Description |
|------|-------------|
| `Single` | One approver required |
| `Multiple` | Multiple approvers, each decides independently |
| `AnyApprover` | First approval completes the step |
| `AllApprovers` | All approvers must approve |
| `RoleBased` | Approval from anyone with a specific role |
| `UserBased` | Approval from specific users |

## Queue Support

```php
->step('long_running', HeavyHandler::class)
    ->queued()
    ->timeout(600)
    ->maxAttempts(5)
```

## Artisan Commands

```bash
php artisan mts:workflow:install           # Publish config and migrations
php artisan mts:workflow:make OrderWorkflow # Generate a workflow class
php artisan mts:workflow:list               # List all registered workflows
php artisan mts:workflow:run {id}           # Run a workflow instance
php artisan mts:workflow:retry {id}         # Retry a failed workflow
php artisan mts:workflow:cancel {id}        # Cancel a running workflow
```

## Events

| Event | Description |
|-------|-------------|
| `WorkflowStarted` | When a workflow instance starts |
| `WorkflowStepStarted` | When a step begins execution |
| `WorkflowStepCompleted` | When a step finishes successfully |
| `WorkflowStepFailed` | When a step fails |
| `WorkflowApproved` | When an approval step is approved |
| `WorkflowRejected` | When an approval step is rejected |
| `WorkflowCompleted` | When all steps complete |
| `WorkflowCancelled` | When a workflow is cancelled |

## Global Helpers

```php
workflow()                              // Get WorkflowManager instance
workflow_start('name', $model)          // Start a workflow
workflow_define('name')                 // Get WorkflowDefinition builder
workflow_retry_delay(1, 'exponential')  // Calculate retry delay
```

## Database Tables

- `mts_workflows` - Workflow definitions
- `mts_workflow_instances` - Running instances
- `mts_workflow_steps` - Step execution records
- `mts_workflow_transitions` - State change history
- `mts_workflow_approvals` - Approval records
- `mts_workflow_logs` - Full audit trail

## Security

- Authorization policies for start, approve, cancel actions
- Database locking for concurrency control
- Idempotency keys for duplicate execution prevention
- Full audit trail with actor, timestamp, IP, request ID

## License

MIT License. See [LICENSE](LICENSE) for details.
