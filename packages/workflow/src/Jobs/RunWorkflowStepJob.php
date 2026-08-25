<?php

declare(strict_types=1);

namespace MageTech\Workflow\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MageTech\Workflow\Engine\WorkflowManager;
use MageTech\Workflow\Engine\WorkflowRunner;
use MageTech\Workflow\Models\WorkflowInstance;

class RunWorkflowStepJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout;

    public int $tries;

    public function __construct(
        public WorkflowInstance $instance,
        public string $stepName,
    ) {
        $this->timeout = config('mts-workflow.timeout', 300);
        $this->tries = config('mts-workflow.retry.max_attempts', 3);
        $this->queue = config('mts-workflow.queue.queue', 'default');
        $this->connection = config('mts-workflow.queue.connection');
    }

    public function handle(WorkflowRunner $runner): void
    {
        $instance = $this->instance->fresh();
        if ($instance === null || $instance->isTerminal()) {
            return;
        }

        $step = $instance->steps()->where('name', $this->stepName)->first();
        if ($step === null || ! $step->isActive()) {
            return;
        }

        $definition = \MageTech\Workflow\Definition\WorkflowDefinition::fromArray($instance->workflow->definition);

        $stepDef = null;
        foreach ($definition->getSteps() as $sd) {
            if ($sd->getName() === $this->stepName) {
                $stepDef = $sd;
                break;
            }
        }

        if ($stepDef === null) {
            return;
        }

        $runner->executeStep($instance, $step, $stepDef);
    }

    public function failed(\Throwable $exception): void
    {
        $instance = $this->instance->fresh();
        if ($instance !== null) {
            $instance->markAsFailed("Step [{$this->stepName}] failed: {$exception->getMessage()}");
        }
    }
}
