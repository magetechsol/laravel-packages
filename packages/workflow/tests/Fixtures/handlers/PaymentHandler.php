<?php

declare(strict_types=1);

namespace MageTech\Workflow\Tests\Fixtures\Handlers;

use MageTech\Workflow\Contracts\WorkflowHandlerContract;
use MageTech\Workflow\Models\WorkflowInstance;
use MageTech\Workflow\Models\WorkflowStep;

class PaymentHandler implements WorkflowHandlerContract
{
    public function handle(WorkflowInstance $instance, WorkflowStep $step): array
    {
        return ['payment_verified' => true, 'payment_amount' => $instance->workflowable->total];
    }
}
