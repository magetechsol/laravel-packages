<?php

declare(strict_types=1);

namespace MageTech\Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MageTech\Workflow\Enums\StepStatus;
use MageTech\Workflow\Enums\StepType;

class WorkflowStep extends Model
{
    protected $table = 'mts_workflow_steps';

    protected $fillable = [
        'instance_id',
        'name',
        'type',
        'status',
        'handler',
        'order',
        'attempts',
        'max_attempts',
        'timeout',
        'result',
        'error',
        'started_at',
        'completed_at',
        'failed_at',
        'next_retry_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => StepType::class,
            'status' => StepStatus::class,
            'result' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
            'next_retry_at' => 'datetime',
        ];
    }

    public function instance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'instance_id');
    }

    public function scopeStatus($query, StepStatus|string $status)
    {
        $value = $status instanceof StepStatus ? $status->value : $status;

        return $query->where('status', $value);
    }

    public function scopeByName($query, string $name)
    {
        return $query->where('name', $name);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function scopePending($query)
    {
        return $query->where('status', StepStatus::Pending);
    }

    public function scopeReadyForRetry($query)
    {
        return $query->where('status', StepStatus::Failed)
            ->where('next_retry_at', '<=', now());
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isTerminal(): bool
    {
        return $this->status->isTerminal();
    }

    public function markAsRunning(): void
    {
        $this->update(['status' => StepStatus::Running, 'started_at' => now()]);
    }

    public function markAsCompleted(?array $result = null): void
    {
        $this->update([
            'status' => StepStatus::Completed,
            'completed_at' => now(),
            'result' => $result,
        ]);
    }

    public function markAsFailed(?string $error = null): void
    {
        $this->update([
            'status' => StepStatus::Failed,
            'failed_at' => now(),
            'error' => $error,
        ]);
    }

    public function markAsSkipped(): void
    {
        $this->update(['status' => StepStatus::Skipped]);
    }

    public function markAsCancelled(): void
    {
        $this->update(['status' => StepStatus::Cancelled]);
    }

    public function incrementAttempts(): void
    {
        $this->attempts = $this->attempts + 1;
        $this->save();
    }

    public function scheduleRetry(\DateTimeInterface $nextRetryAt): void
    {
        $this->update([
            'status' => StepStatus::Pending,
            'next_retry_at' => $nextRetryAt,
        ]);
    }
}
