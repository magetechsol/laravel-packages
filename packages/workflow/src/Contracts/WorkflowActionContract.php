<?php

declare(strict_types=1);

namespace MageTech\Workflow\Contracts;

use MageTech\Workflow\Models\WorkflowInstance;
use MageTech\Workflow\Models\WorkflowStep;

interface WorkflowActionContract
{
    /**
     * Execute a workflow action (pre/post step hook).
     */
    public function execute(WorkflowInstance $instance, WorkflowStep $step, array $context): void;
}
