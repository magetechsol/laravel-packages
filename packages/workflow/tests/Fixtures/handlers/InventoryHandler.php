<?php

declare(strict_types=1);

namespace MageTech\Workflow\Tests\Fixtures\Handlers;

use MageTech\Workflow\Contracts\WorkflowHandlerContract;
use MageTech\Workflow\Models\WorkflowInstance;
use MageTech\Workflow\Models\WorkflowStep;

class InventoryHandler implements WorkflowHandlerContract
{
    public function handle(WorkflowInstance $instance, WorkflowStep $step): array
    {
        return ['inventory_checked' => true];
    }
}
