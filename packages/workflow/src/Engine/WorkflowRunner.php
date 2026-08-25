<?php

declare(strict_types=1);

namespace MageTech\Workflow\Engine;

use MageTech\Workflow\Audit\AuditLogger;
use MageTech\Workflow\Approvals\ApprovalManager;
use MageTech\Workflow\Definition\WorkflowDefinition;
use MageTech\Workflow\Definition\WorkflowStepDefinition;
use MageTech\Workflow\Enums\StepStatus;
use MageTech\Workflow\Enums\StepType;
use MageTech\Workflow\Enums\TransitionType;
use MageTech\Workflow\Enums\WorkflowStatus;
use MageTech\Workflow\Events\WorkflowStepCompleted;
use MageTech\Workflow\Events\WorkflowStepFailed;
use MageTech\Workflow\Events\WorkflowStepStarted;
use MageTech\Workflow\Exceptions\WorkflowStepException;
use MageTech\Workflow\Models\WorkflowInstance;
use MageTech\Workflow\Models\WorkflowStep;
use MageTech\Workflow\Support\RetryStrategy;

class WorkflowRunner
{
    public function __construct(
        private ConditionEvaluator $evaluator,
        private ConcurrencyGuard $guard,
        private ApprovalManager $approvals,
        private AuditLogger $audit,
    ) {}

    /**
     * Execute a workflow instance from its current step.
     */
    public function run(WorkflowInstance $instance): void
    {
        $definition = WorkflowDefinition::fromArray($instance->workflow->definition);

        if (! $this->evaluator->evaluateWhenCondition($definition->getWhenCondition(), $instance)) {
            $this->audit->log($instance, TransitionType::Cancelled, reason: 'Workflow when condition not met');
            $instance->markAsCancelled();
            return;
        }

        if (! $this->evaluator->evaluateUnlessCondition($definition->getUnlessCondition(), $instance)) {
            $this->audit->log($instance, TransitionType::Cancelled, reason: 'Workflow unless condition not met');
            $instance->markAsCancelled();
            return;
        }

        $steps = $this->getOrderedSteps($instance);
        $currentOrder = $this->getCurrentOrder($instance);

        foreach ($steps as $step) {
            if ($step->order < $currentOrder) {
                continue;
            }

            if ($step->status->isTerminal() && $step->status !== StepStatus::Completed) {
                return;
            }

            if ($step->status === StepStatus::Completed) {
                continue;
            }

            $stepDef = $this->findStepDefinition($definition, $step->name);
            if ($stepDef === null) {
                continue;
            }

            if (! $this->evaluator->evaluateStepCondition($stepDef->getCondition(), $instance)) {
                $step->markAsSkipped();
                $this->audit->log($instance, TransitionType::StepSkipped, stepName: $step->name, fromState: StepStatus::Pending->value, toState: StepStatus::Skipped->value);
                continue;
            }

            if ($stepDef->getType() === StepType::Approval) {
                $this->handleApprovalStep($instance, $step, $stepDef);
                return;
            }

            $this->executeStep($instance, $step, $stepDef);

            if ($step->status !== StepStatus::Completed) {
                return;
            }
        }

        if ($this->allStepsCompleted($instance)) {
            $instance->markAsCompleted();
            $this->audit->log($instance, TransitionType::Completed, fromState: WorkflowStatus::Running->value, toState: WorkflowStatus::Completed->value);
        }
    }

    /**
     * Execute a single step.
     */
    public function executeStep(WorkflowInstance $instance, WorkflowStep $step, WorkflowStepDefinition $stepDef): void
    {
        $this->guard->lock($instance, function ($locked) use ($instance, $step, $stepDef) {
            $this->guard->lockStep($step, function ($lockedStep) use ($instance, $stepDef) {
                $step->markAsRunning();
                $instance->update(['current_step' => $step->name]);

                $this->audit->log($instance, TransitionType::StepStarted, stepName: $step->name, fromState: StepStatus::Pending->value, toState: StepStatus::Running->value);
                event(new WorkflowStepStarted($instance, $step));

                $handler = $stepDef->getHandler();
                if ($handler === null) {
                    $step->markAsCompleted();
                    $this->audit->log($instance, TransitionType::StepCompleted, stepName: $step->name, fromState: StepStatus::Running->value, toState: StepStatus::Completed->value);
                    event(new WorkflowStepCompleted($instance, $step));
                    return;
                }

                try {
                    $result = (new $handler())->handle($instance, $step);
                    $step->markAsCompleted($result);

                    if (is_array($result) && $result !== []) {
                        $context = array_merge($instance->context ?? [], $result);
                        $instance->update(['context' => $context]);
                    }

                    $this->audit->log($instance, TransitionType::StepCompleted, stepName: $step->name, fromState: StepStatus::Running->value, toState: StepStatus::Completed->value, metadata: $result);
                    event(new WorkflowStepCompleted($instance, $step));
                } catch (\Throwable $e) {
                    $step->incrementAttempts();

                    if ($step->attempts >= $step->max_attempts) {
                        $step->markAsFailed($e->getMessage());
                        $instance->markAsFailed($e->getMessage());
                        $this->audit->log($instance, TransitionType::StepFailed, stepName: $step->name, fromState: StepStatus::Running->value, toState: StepStatus::Failed->value, reason: $e->getMessage());
                        $this->audit->log($instance, TransitionType::Failed, fromState: WorkflowStatus::Running->value, toState: WorkflowStatus::Failed->value, reason: $e->getMessage());
                        event(new WorkflowStepFailed($instance, $step, $e));
                        return;
                    }

                    $retryStrategy = app(RetryStrategy::class);
                    $nextRetry = $retryStrategy->calculateNextRetry(
                        $step->attempts,
                        $stepDef->getBackoff(),
                        $stepDef->getBaseDelay(),
                    );
                    $step->scheduleRetry($nextRetry);
                    $this->audit->log($instance, TransitionType::Retried, stepName: $step->name, reason: $e->getMessage(), metadata: ['next_retry_at' => $nextRetry->toIso8601String()]);
                    event(new WorkflowStepFailed($instance, $step, $e));
                }
            });
        });
    }

    /**
     * Handle an approval step.
     */
    private function handleApprovalStep(
        WorkflowInstance $instance,
        WorkflowStep $step,
        WorkflowStepDefinition $stepDef,
    ): void {
        if ($step->status === StepStatus::Pending) {
            $step->markAsRunning();
            $instance->update(['current_step' => $step->name]);

            $this->approvals->createApprovals(
                $instance,
                $step->name,
                $stepDef->getApprovalType(),
                $stepDef->getApprovers(),
                $stepDef->getApproverRole(),
                timeout: $stepDef->getTimeout(),
            );

            $this->audit->log($instance, TransitionType::StepStarted, stepName: $step->name, fromState: StepStatus::Pending->value, toState: StepStatus::Running->value);
            event(new WorkflowStepStarted($instance, $step));
        }
    }

    /**
     * Approve a step and continue the workflow.
     */
    public function approveStep(WorkflowInstance $instance, string $stepName, ?int $approverId = null, ?string $comment = null): void
    {
        $this->approvals->approve($instance, $stepName, $approverId, $comment);

        $step = $instance->steps()->where('name', $stepName)->firstOrFail();
        $step->markAsCompleted();

        $this->audit->log($instance, TransitionType::StepCompleted, stepName: $stepName, fromState: StepStatus::Running->value, toState: StepStatus::Completed->value);
        $this->audit->log($instance, TransitionType::Approved, stepName: $stepName, reason: $comment);

        event(new WorkflowStepCompleted($instance, $step));

        $this->run($instance);
    }

    /**
     * Reject a step.
     */
    public function rejectStep(WorkflowInstance $instance, string $stepName, ?int $approverId = null, ?string $comment = null): void
    {
        $this->approvals->reject($instance, $stepName, $approverId, $comment);

        $step = $instance->steps()->where('name', $stepName)->firstOrFail();
        $step->markAsFailed($comment);

        $this->audit->log($instance, TransitionType::StepFailed, stepName: $stepName, reason: $comment);
        $this->audit->log($instance, TransitionType::Rejected, stepName: $stepName, reason: $comment);

        event(new WorkflowStepFailed($instance, $step));
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, WorkflowStep> */
    private function getOrderedSteps(WorkflowInstance $instance)
    {
        return $instance->steps()->ordered()->get();
    }

    private function getCurrentOrder(WorkflowInstance $instance): int
    {
        $currentStep = $instance->current_step;
        if ($currentStep === null) {
            return 0;
        }

        $step = $instance->steps()->where('name', $currentStep)->first();
        return $step?->order ?? 0;
    }

    private function findStepDefinition(WorkflowDefinition $definition, string $name): ?WorkflowStepDefinition
    {
        foreach ($definition->getSteps() as $step) {
            if ($step->getName() === $name) {
                return $step;
            }
        }

        return null;
    }

    private function allStepsCompleted(WorkflowInstance $instance): bool
    {
        return ! $instance->steps()
            ->whereNotIn('status', [StepStatus::Completed, StepStatus::Skipped])
            ->exists();
    }
}
