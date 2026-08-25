# Use Cases

Industry-specific examples for the MTS Laravel Workflow Engine.

## E-commerce Order Processing

```php
WorkflowDefinition::define('order-processing')
    ->step('validate', ValidateOrderHandler::class)
    ->step('payment', PaymentHandler::class)
        ->timeout(30)
        ->maxAttempts(3)
        ->retry(3, 'exponential')
    ->step('inventory', InventoryHandler::class)
        ->when(fn ($order) => $order->requires_stock_check)
    ->step('fulfillment', FulfillmentHandler::class)
    ->step('notify', NotificationHandler::class)
    ->complete();
```

## Employee Onboarding

```php
WorkflowDefinition::define('employee-onboarding')
    ->step('create_accounts', AccountCreationHandler::class)
    ->step('assign_equipment', EquipmentHandler::class)
    ->approval('hr-approval', ApprovalType::AllApprovers)
        ->approvers([$hrManager->id])
        ->timeout(172800)
    ->step('training', TrainingAssignmentHandler::class)
    ->step('welcome', WelcomeEmailHandler::class)
    ->complete();
```

## Content Publishing

```php
WorkflowDefinition::define('content-publishing')
    ->step('draft', DraftHandler::class)
    ->approval('editor-review', ApprovalType::AnyApprover)
        ->approvers($editorIds)
        ->timeout(259200)
    ->approval('legal-review', ApprovalType::Single)
        ->approvers([$legalTeam->id])
        ->when(fn ($post) => $post->contains_sponsored_content)
    ->step('publish', PublishHandler::class)
    ->step('distribute', DistributionHandler::class)
    ->complete();
```

## Invoice Approval

```php
WorkflowDefinition::define('invoice-approval')
    ->step('validate', ValidateInvoiceHandler::class)
    ->step('check-budget', BudgetCheckHandler::class)
    ->approval('manager', ApprovalType::Single)
        ->approvers(fn ($invoice) => [$invoice->department->manager_id])
        ->timeout(86400)
    ->approval('finance', ApprovalType::AnyApprover)
        ->approvers(fn ($invoice) => $invoice->financeTeam->pluck('id')->toArray())
        ->when(fn ($invoice) => $invoice->amount > 10000)
    ->step('process-payment', ProcessPaymentHandler::class)
    ->complete();
```

## IT Change Management

```php
WorkflowDefinition::define('it-change-request')
    ->step('assess-impact', ImpactAssessmentHandler::class)
    ->step('security-review', SecurityReviewHandler::class)
    ->approval('change-advisory-board', ApprovalType::AllApprovers)
        ->approvers($cabMemberIds)
        ->timeout(604800)
    ->step('schedule', ScheduleChangeHandler::class)
        ->queued()
        ->timeout(3600)
    ->step('execute', ExecuteChangeHandler::class)
        ->queued()
        ->timeout(7200)
    ->step('verify', VerificationHandler::class)
    ->step('close', CloseTicketHandler::class)
    ->complete();
```

## Multi-Tenant SaaS Provisioning

```php
WorkflowDefinition::define('tenant-provisioning')
    ->step('validate-plan', ValidatePlanHandler::class)
    ->step('create-tenant', CreateTenantHandler::class)
    ->step('provision-database', ProvisionDatabaseHandler::class)
        ->queued()
        ->timeout(300)
    ->step('configure-features', ConfigureFeaturesHandler::class)
    ->step('send-credentials', SendCredentialsHandler::class)
    ->step('activate', ActivationHandler::class)
    ->complete();
```
