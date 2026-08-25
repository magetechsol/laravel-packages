<?php

declare(strict_types=1);

namespace MageTech\Workflow\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use MageTech\Workflow\Models\WorkflowInstance;

class WorkflowRejected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public WorkflowInstance $instance,
        public string $stepName,
        public ?int $approverId = null,
        public ?string $reason = null,
    ) {}
}
