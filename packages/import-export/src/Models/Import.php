<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MageTech\ImportExport\Enums\ImportStatus;

class Import extends Model
{
    protected $fillable = [
        'name',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'status',
        'total_rows',
        'processed_rows',
        'successful_rows',
        'failed_rows',
        'skipped_rows',
        'options',
        'mapping',
        'error_summary',
        'error_report_path',
        'created_by',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'total_rows' => 'integer',
            'processed_rows' => 'integer',
            'successful_rows' => 'integer',
            'failed_rows' => 'integer',
            'skipped_rows' => 'integer',
            'status' => ImportStatus::class,
            'options' => 'array',
            'mapping' => 'array',
            'error_summary' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function rows(): HasMany
    {
        return $this->hasMany(ImportRow::class);
    }

    public function errors(): HasMany
    {
        return $this->hasMany(ImportError::class);
    }

    public function failedRows(): HasMany
    {
        return $this->rows()->where('status', 'failed');
    }

    public function pendingRows(): HasMany
    {
        return $this->rows()->where('status', 'pending');
    }

    public function scopeStatus($query, ImportStatus|string $status)
    {
        $value = $status instanceof ImportStatus ? $status->value : $status;

        return $query->where('status', $value);
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeFailed($query)
    {
        return $query->where('status', ImportStatus::Failed);
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isTerminal(): bool
    {
        return $this->status->isTerminal();
    }

    public function canCancel(): bool
    {
        return $this->status->canCancel();
    }

    public function percentage(): float
    {
        if ($this->total_rows === 0) {
            return 0.0;
        }

        return round(($this->processed_rows / $this->total_rows) * 100, 2);
    }

    public function markAsProcessing(): void
    {
        $this->update([
            'status' => ImportStatus::Processing,
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => ImportStatus::Completed,
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed(string $summary = ''): void
    {
        $this->update([
            'status' => ImportStatus::Failed,
            'completed_at' => now(),
            'error_summary' => ['message' => $summary],
        ]);
    }

    public function markAsCancelled(): void
    {
        $this->update([
            'status' => ImportStatus::Cancelled,
            'completed_at' => now(),
        ]);
    }
}
