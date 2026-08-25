<?php

declare(strict_types=1);

namespace MageTech\Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use MageTech\Workflow\Enums\WorkflowStatus;

class WorkflowInstance extends Model
{
    protected $table = 'mts_workflow_instances';

    protected $fillable = [
        'workflow_id',
        'workflowable_type',
        'workflowable_id',
        'current_step',
        'status',
        'context',
        'started_by',
        'started_at',
        'completed_at',
        'failed_at',
        'cancelled_at',
        'error',
        'request_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => WorkflowStatus::class,
            'context' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function workflowable(): MorphTo
    {
        return $this->morphTo();
    }

    public function steps(): HasMany
    {
        return $this->hasMany(WorkflowStep::class, 'instance_id');
    }

    public function transitions(): HasMany
    {
        return $this->hasMany(WorkflowTransition::class, 'instance_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(WorkflowApproval::class, 'instance_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WorkflowLog::class, 'instance_id');
    }

    public function scopeStatus($query, WorkflowStatus|string $status)
    {
        $value = $status instanceof WorkflowStatus ? $status->value : $status;

        return $query->where('status', $value);
    }

    public function scopeForWorkflowable($query, string $type, int|string $id)
    {
        return $query->where('workflowable_type', $type)
            ->where('workflowable_id', $id);
    }

    public function scopeReadyForRetry($query)
    {
        return $query->where('status', WorkflowStatus::Running)
            ->whereHas('steps', function ($q) {
                $q->where('status', \MageTech\Workflow\Enums\StepStatus::Failed)
                    ->where('next_retry_at', '<=', now());
            });
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isTerminal(): bool
    {
        return $this->status->isTerminal();
    }

    public function canRetry(): bool
    {
        return $this->status->canRetry();
    }

    public function canCancel(): bool
    {
        return $this->status->canCancel();
    }

    public function markAsRunning(): void
    {
        $this->update(['status' => WorkflowStatus::Running, 'started_at' => now()]);
    }

    public function markAsCompleted(): void
    {
        $this->update(['status' => WorkflowStatus::Completed, 'completed_at' => now()]);
    }

    public function markAsFailed(?string $error = null): void
    {
        $this->update(['status' => WorkflowStatus::Failed, 'failed_at' => now(), 'error' => $error]);
    }

    public function markAsCancelled(): void
    {
        $this->update(['status' => WorkflowStatus::Cancelled, 'cancelled_at' => now()]);
    }

    public function getContextData(string $key, mixed $default = null): mixed
    {
        return data_get($this->context, $key, $default);
    }

    public function setContextData(string $key, mixed $value): void
    {
        $this->context = data_set($this->context ?? [], $key, $value);
        $this->save();
    }
}
