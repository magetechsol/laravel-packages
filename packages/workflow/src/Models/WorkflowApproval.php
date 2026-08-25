<?php

declare(strict_types=1);

namespace MageTech\Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MageTech\Workflow\Enums\ApprovalStatus;
use MageTech\Workflow\Enums\ApprovalType;

class WorkflowApproval extends Model
{
    protected $table = 'mts_workflow_approvals';

    protected $fillable = [
        'instance_id',
        'step_name',
        'approval_type',
        'approver_id',
        'approver_type',
        'status',
        'decision',
        'comment',
        'decided_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'approval_type' => ApprovalType::class,
            'status' => ApprovalStatus::class,
            'decided_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function instance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'instance_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', ApprovalStatus::Pending);
    }

    public function scopeByStep($query, string $stepName)
    {
        return $query->where('step_name', $stepName);
    }

    public function scopeByApprover($query, int $approverId)
    {
        return $query->where('approver_id', $approverId);
    }

    public function isPending(): bool
    {
        return $this->status === ApprovalStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status === ApprovalStatus::Approved;
    }

    public function isRejected(): bool
    {
        return $this->status === ApprovalStatus::Rejected;
    }

    public function isExpired(): bool
    {
        return $this->status === ApprovalStatus::Expired
            || ($this->expires_at !== null && $this->expires_at->isPast());
    }

    public function approve(?string $comment = null, ?int $actorId = null): void
    {
        $this->update([
            'status' => ApprovalStatus::Approved,
            'decision' => 'approved',
            'comment' => $comment,
            'decided_at' => now(),
        ]);
    }

    public function reject(?string $comment = null, ?int $actorId = null): void
    {
        $this->update([
            'status' => ApprovalStatus::Rejected,
            'decision' => 'rejected',
            'comment' => $comment,
            'decided_at' => now(),
        ]);
    }

    public function expire(): void
    {
        $this->update([
            'status' => ApprovalStatus::Expired,
            'decided_at' => now(),
        ]);
    }
}
