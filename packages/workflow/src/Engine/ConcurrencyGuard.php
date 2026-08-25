<?php

declare(strict_types=1);

namespace MageTech\Workflow\Engine;

use Illuminate\Support\Facades\DB;
use MageTech\Workflow\Exceptions\WorkflowConcurrencyException;
use MageTech\Workflow\Models\WorkflowInstance;
use MageTech\Workflow\Models\WorkflowStep;

class ConcurrencyGuard
{
    /**
     * Execute a callback with a lock on the workflow instance.
     *
     * Uses database row-level locking to prevent concurrent step execution.
     *
     * @template T
     *
     * @param  callable(WorkflowInstance): T  $callback
     * @return T
     */
    public function lock(WorkflowInstance $instance, callable $callback): mixed
    {
        if (! config('mts-workflow.concurrency.enabled', true)) {
            return $callback($instance);
        }

        return DB::transaction(function () use ($instance, $callback) {
            /** @var WorkflowInstance $locked */
            $locked = WorkflowInstance::query()
                ->where('id', $instance->id)
                ->lockForUpdate()
                ->first();

            if ($locked === null) {
                throw WorkflowConcurrencyException::instanceLocked($instance->id);
            }

            return $callback($locked);
        });
    }

    /**
     * Execute a callback with a lock on a specific workflow step.
     *
     * @template T
     *
     * @param  callable(WorkflowStep): T  $callback
     * @return T
     */
    public function lockStep(WorkflowStep $step, callable $callback): mixed
    {
        if (! config('mts-workflow.concurrency.enabled', true)) {
            return $callback($step);
        }

        return DB::transaction(function () use ($step, $callback) {
            /** @var WorkflowStep $locked */
            $locked = WorkflowStep::query()
                ->where('id', $step->id)
                ->lockForUpdate()
                ->first();

            if ($locked === null) {
                throw WorkflowConcurrencyException::stepLocked($step->name, $step->instance_id);
            }

            if (! $locked->isActive()) {
                throw WorkflowConcurrencyException::stepLocked($locked->name, $locked->instance_id);
            }

            return $callback($locked);
        });
    }
}
