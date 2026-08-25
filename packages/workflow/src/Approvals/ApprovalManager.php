<?php

declare(strict_types=1);

namespace MageTech\Workflow\Approvals;

use Illuminate\Support\Facades\DB;
use MageTech\Workflow\Enums\ApprovalStatus;
use MageTech\Workflow\Enums\ApprovalType;
use MageTech\Workflow\Models\WorkflowApproval;
use MageTech\Workflow\Models\WorkflowInstance;

class ApprovalManager
{
    /**
     * Create approval records for an approval step.
     *
     * @param  array<int, int|string>  $approvers
     */
    public function createApprovals(
        WorkflowInstance $instance,
        string $stepName,
        ApprovalType $type,
        array $approvers = [],
        ?string $approverRole = null,
        int $timeout = 86400,
    ): void {
        $expiresAt = $timeout > 0 ? now()->addSeconds($timeout) : null;

        match ($type) {
            ApprovalType::Single, ApprovalType::UserBased => $this->createSingleApproval($instance, $stepName, $type, $approvers, $expiresAt),
            ApprovalType::Multiple, ApprovalType::AnyApprover, ApprovalType::AllApprovers => $this->createMultipleApprovals($instance, $stepName, $type, $approvers, $expiresAt),
            ApprovalType::RoleBased => $this->createRoleBasedApproval($instance, $stepName, $approverRole, $expiresAt),
        };
    }

    private function createSingleApproval(
        WorkflowInstance $instance,
        string $stepName,
        ApprovalType $type,
        array $approvers,
        ?\DateTimeInterface $expiresAt,
    ): void {
        $approverId = $approvers[0] ?? null;

        WorkflowApproval::create([
            'instance_id' => $instance->id,
            'step_name' => $stepName,
            'approval_type' => $type,
            'approver_id' => $approverId,
            'status' => ApprovalStatus::Pending,
            'expires_at' => $expiresAt,
        ]);
    }

    private function createMultipleApprovals(
        WorkflowInstance $instance,
        string $stepName,
        ApprovalType $type,
        array $approvers,
        ?\DateTimeInterface $expiresAt,
    ): void {
        foreach ($approvers as $approverId) {
            WorkflowApproval::create([
                'instance_id' => $instance->id,
                'step_name' => $stepName,
                'approval_type' => $type,
                'approver_id' => $approverId,
                'status' => ApprovalStatus::Pending,
                'expires_at' => $expiresAt,
            ]);
        }
    }

    private function createRoleBasedApproval(
        WorkflowInstance $instance,
        string $stepName,
        ?string $role,
        ?\DateTimeInterface $expiresAt,
    ): void {
        WorkflowApproval::create([
            'instance_id' => $instance->id,
            'step_name' => $stepName,
            'approval_type' => ApprovalType::RoleBased,
            'approver_type' => $role,
            'status' => ApprovalStatus::Pending,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Approve a step.
     */
    public function approve(
        WorkflowInstance $instance,
        string $stepName,
        ?int $approverId = null,
        ?string $comment = null,
    ): void {
        $approval = $this->findPendingApproval($instance, $stepName, $approverId);

        if ($approval === null) {
            return;
        }

        $approval->approve($comment, $approverId);

        $stepDef = $this->findStepDefinition($instance, $stepName);
        if ($stepDef !== null && $stepDef->requiresAll()) {
            $remaining = WorkflowApproval::where('instance_id', $instance->id)
                ->where('step_name', $stepName)
                ->where('status', ApprovalStatus::Pending)
                ->count();

            if ($remaining > 0) {
                return;
            }
        }
    }

    /**
     * Reject a step.
     */
    public function reject(
        WorkflowInstance $instance,
        string $stepName,
        ?int $approverId = null,
        ?string $comment = null,
    ): void {
        $approval = $this->findPendingApproval($instance, $stepName, $approverId);

        if ($approval === null) {
            return;
        }

        $approval->reject($comment, $approverId);

        WorkflowApproval::where('instance_id', $instance->id)
            ->where('step_name', $stepName)
            ->where('status', ApprovalStatus::Pending)
            ->update(['status' => ApprovalStatus::Cancelled->value]);
    }

    /**
     * Check if an approval step is fully approved.
     */
    public function isApproved(WorkflowInstance $instance, string $stepName): bool
    {
        $pending = WorkflowApproval::where('instance_id', $instance->id)
            ->where('step_name', $stepName)
            ->where('status', ApprovalStatus::Pending)
            ->count();

        if ($pending === 0) {
            $approved = WorkflowApproval::where('instance_id', $instance->id)
                ->where('step_name', $stepName)
                ->where('status', ApprovalStatus::Approved)
                ->count();

            return $approved > 0;
        }

        return false;
    }

    /**
     * Check if an approval step has been rejected.
     */
    public function isRejected(WorkflowInstance $instance, string $stepName): bool
    {
        return WorkflowApproval::where('instance_id', $instance->id)
            ->where('step_name', $stepName)
            ->where('status', ApprovalStatus::Rejected)
            ->exists();
    }

    /**
     * Expire all pending approvals that have passed their expiry.
     */
    public function expireOverdue(): int
    {
        return WorkflowApproval::where('status', ApprovalStatus::Pending)
            ->where('expires_at', '<=', now())
            ->update(['status' => ApprovalStatus::Expired->value, 'decided_at' => now()]);
    }

    private function findPendingApproval(
        WorkflowInstance $instance,
        string $stepName,
        ?int $approverId,
    ): ?WorkflowApproval {
        $query = WorkflowApproval::where('instance_id', $instance->id)
            ->where('step_name', $stepName)
            ->where('status', ApprovalStatus::Pending);

        if ($approverId !== null) {
            $query->where('approver_id', $approverId);
        }

        return $query->first();
    }

    private function findStepDefinition(WorkflowInstance $instance, string $stepName): ?\MageTech\Workflow\Definition\WorkflowStepDefinition
    {
        $definition = \MageTech\Workflow\Definition\WorkflowDefinition::fromArray($instance->workflow->definition);

        foreach ($definition->getSteps() as $step) {
            if ($step->getName() === $stepName) {
                return $step;
            }
        }

        return null;
    }
}
