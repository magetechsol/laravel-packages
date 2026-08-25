<?php

declare(strict_types=1);

namespace MageTech\Workflow\Engine;

use Illuminate\Support\Facades\DB;
use MageTech\Workflow\Audit\AuditLogger;
use MageTech\Workflow\Definition\WorkflowDefinition;
use MageTech\Workflow\Enums\StepStatus;
use MageTech\Workflow\Enums\StepType;
use MageTech\Workflow\Enums\TransitionType;
use MageTech\Workflow\Enums\WorkflowStatus;
use MageTech\Workflow\Events\WorkflowStarted;
use MageTech\Workflow\Events\WorkflowCancelled;
use MageTech\Workflow\Exceptions\WorkflowNotFoundException;
use MageTech\Workflow\Exceptions\WorkflowDefinitionException;
use MageTech\Workflow\Models\Workflow;
use MageTech\Workflow\Models\WorkflowInstance;
use MageTech\Workflow\Models\WorkflowStep;

class WorkflowManager
{
    public function __construct(
        private WorkflowRegistrar $registrar,
        private WorkflowRunner $runner,
        private WorkflowRepository $repository,
        private AuditLogger $audit,
    ) {}

    /**
     * Start a new workflow instance for a model.
     */
    public function start(
        string $workflowName,
        $model,
        ?int $startedBy = null,
        ?string $requestId = null,
        ?array $initialContext = null,
    ): WorkflowInstance {
        $definition = $this->registrar->get($workflowName);
        $definition->validate();

        return DB::transaction(function () use ($definition, $model, $startedBy, $requestId, $initialContext) {
            $workflow = Workflow::firstOrCreate(
                ['name' => $definition->getName()],
                [
                    'description' => $definition->getDescription(),
                    'definition' => $definition->toArray(),
                    'is_active' => true,
                ]
            );

            $instance = WorkflowInstance::create([
                'workflow_id' => $workflow->id,
                'workflowable_type' => get_class($model),
                'workflowable_id' => $model->getKey(),
                'status' => WorkflowStatus::Running,
                'context' => $initialContext ?? [],
                'started_by' => $startedBy,
                'started_at' => now(),
                'request_id' => $requestId,
            ]);

            $order = 0;
            foreach ($definition->getSteps() as $stepDef) {
                WorkflowStep::create([
                    'instance_id' => $instance->id,
                    'name' => $stepDef->getName(),
                    'type' => $stepDef->getType(),
                    'status' => StepStatus::Pending,
                    'handler' => $stepDef->getHandler(),
                    'order' => $order++,
                    'max_attempts' => $stepDef->getMaxAttempts(),
                    'timeout' => $stepDef->getTimeout(),
                ]);
            }

            $this->audit->log($instance, TransitionType::Started, fromState: WorkflowStatus::Draft->value, toState: WorkflowStatus::Running->value, actorId: $startedBy);
            event(new WorkflowStarted($instance));

            $this->runner->run($instance->fresh());

            return $instance->fresh();
        });
    }

    /**
     * Approve a workflow step.
     */
    public function approve(
        int|string $instanceId,
        string $stepName,
        ?int $approverId = null,
        ?string $comment = null,
    ): WorkflowInstance {
        $instance = $this->repository->find($instanceId);

        $this->runner->approveStep($instance, $stepName, $approverId, $comment);

        return $instance->fresh();
    }

    /**
     * Reject a workflow step.
     */
    public function reject(
        int|string $instanceId,
        string $stepName,
        ?int $approverId = null,
        ?string $comment = null,
    ): WorkflowInstance {
        $instance = $this->repository->find($instanceId);

        $this->runner->rejectStep($instance, $stepName, $approverId, $comment);

        return $instance->fresh();
    }

    /**
     * Cancel a workflow instance.
     */
    public function cancel(int|string $instanceId, ?int $actorId = null, ?string $reason = null): WorkflowInstance
    {
        $instance = $this->repository->find($instanceId);

        if (! $instance->canCancel()) {
            throw new WorkflowDefinitionException("Cannot cancel workflow in status [{$instance->status->value}].");
        }

        DB::transaction(function () use ($instance, $actorId, $reason) {
            $instance->markAsCancelled();

            $instance->steps()
                ->whereNotIn('status', [StepStatus::Completed, StepStatus::Skipped])
                ->update(['status' => StepStatus::Cancelled->value]);

            $this->audit->log(
                $instance,
                TransitionType::Cancelled,
                fromState: WorkflowStatus::Running->value,
                toState: WorkflowStatus::Cancelled->value,
                actorId: $actorId,
                reason: $reason,
            );

            event(new WorkflowCancelled($instance));
        });

        return $instance->fresh();
    }

    /**
     * Retry a failed workflow.
     */
    public function retry(int|string $instanceId): WorkflowInstance
    {
        $instance = $this->repository->find($instanceId);

        if (! $instance->canRetry()) {
            throw new WorkflowDefinitionException("Cannot retry workflow in status [{$instance->status->value}].");
        }

        DB::transaction(function () use ($instance) {
            $instance->update([
                'status' => WorkflowStatus::Running,
                'error' => null,
                'failed_at' => null,
            ]);

            $instance->steps()
                ->where('status', StepStatus::Failed)
                ->update([
                    'status' => StepStatus::Pending->value,
                    'error' => null,
                    'next_retry_at' => null,
                ]);

            $this->audit->log($instance, TransitionType::Retried, fromState: WorkflowStatus::Failed->value, toState: WorkflowStatus::Running->value);
        });

        $this->runner->run($instance->fresh());

        return $instance->fresh();
    }

    /**
     * Get a workflow instance.
     */
    public function get(int|string $instanceId): WorkflowInstance
    {
        return $this->repository->find($instanceId);
    }
}
