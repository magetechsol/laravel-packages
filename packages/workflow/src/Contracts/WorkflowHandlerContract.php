<?php

declare(strict_types=1);

namespace MageTech\Workflow\Contracts;

use MageTech\Workflow\Models\WorkflowInstance;
use MageTech\Workflow\Models\WorkflowStep;

interface WorkflowHandlerContract
{
    /**
     * Execute the workflow step logic.
     *
     * @return array<string, mixed> Step output data that will be merged into instance context.
     */
    public function handle(WorkflowInstance $instance, WorkflowStep $step): array;
}
