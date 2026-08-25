<?php

declare(strict_types=1);

namespace MageTech\Workflow\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use MageTech\Workflow\Models\WorkflowInstance;

class WorkflowCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public WorkflowInstance $instance) {}
}
