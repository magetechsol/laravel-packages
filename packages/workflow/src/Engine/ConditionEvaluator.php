<?php

declare(strict_types=1);

namespace MageTech\Workflow\Engine;

use Closure;
use Illuminate\Support\Facades\DB;
use MageTech\Workflow\Definition\WorkflowStepDefinition;
use MageTech\Workflow\Enums\StepStatus;
use MageTech\Workflow\Enums\StepType;
use MageTech\Workflow\Models\WorkflowInstance;
use MageTech\Workflow\Models\WorkflowStep;

class ConditionEvaluator
{
    /**
     * Evaluate a step's condition against the instance context.
     */
    public function evaluateStepCondition(?Closure $condition, WorkflowInstance $instance): bool
    {
        if ($condition === null) {
            return true;
        }

        $context = $instance->context ?? [];

        return (bool) $condition($instance->workflowable, $context, $instance);
    }

    /**
     * Evaluate a global when condition.
     */
    public function evaluateWhenCondition(?Closure $condition, WorkflowInstance $instance): bool
    {
        if ($condition === null) {
            return true;
        }

        return $this->evaluateStepCondition($condition, $instance);
    }

    /**
     * Evaluate a global unless condition.
     */
    public function evaluateUnlessCondition(?Closure $condition, WorkflowInstance $instance): bool
    {
        if ($condition === null) {
            return true;
        }

        return ! $this->evaluateStepCondition($condition, $instance);
    }
}
