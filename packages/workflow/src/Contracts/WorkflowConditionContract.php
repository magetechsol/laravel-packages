<?php

declare(strict_types=1);

namespace MageTech\Workflow\Contracts;

use MageTech\Workflow\Models\WorkflowInstance;

interface WorkflowConditionContract
{
    /**
     * Determine if the condition is met.
     */
    public function evaluate(WorkflowInstance $instance, array $context): bool;
}
