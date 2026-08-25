<?php

declare(strict_types=1);

namespace MageTech\Workflow\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MageTech\Workflow\Approvals\ApprovalManager;

class ExpireWorkflowApprovalsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 60;

    public function __construct()
    {
        $this->tries = 1;
        $this->queue = config('mts-workflow.queue.queue', 'default');
        $this->connection = config('mts-workflow.queue.connection');
    }

    public function handle(ApprovalManager $manager): void
    {
        $manager->expireOverdue();
    }
}
