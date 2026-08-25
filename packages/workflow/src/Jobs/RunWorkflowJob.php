<?php

declare(strict_types=1);

namespace MageTech\Workflow\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MageTech\Workflow\Engine\WorkflowRunner;
use MageTech\Workflow\Models\WorkflowInstance;

class RunWorkflowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout;

    public int $tries;

    public function __construct(
        public WorkflowInstance $instance,
    ) {
        $this->timeout = config('mts-workflow.timeout', 300);
        $this->tries = 1;
        $this->queue = config('mts-workflow.queue.queue', 'default');
        $this->connection = config('mts-workflow.queue.connection');
    }

    public function handle(WorkflowRunner $runner): void
    {
        $instance = $this->instance->fresh();
        if ($instance === null || $instance->isTerminal()) {
            return;
        }

        $runner->run($instance);
    }

    public function failed(\Throwable $exception): void
    {
        $instance = $this->instance->fresh();
        if ($instance !== null) {
            $instance->markAsFailed("Workflow failed: {$exception->getMessage()}");
        }
    }
}
