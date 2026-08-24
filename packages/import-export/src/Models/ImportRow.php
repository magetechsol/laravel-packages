<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MageTech\ImportExport\Enums\ImportRowStatus;

class ImportRow extends Model
{
    protected $fillable = [
        'import_id',
        'row_number',
        'data',
        'mapped_data',
        'status',
        'error_message',
        'error_details',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'row_number' => 'integer',
            'data' => 'array',
            'mapped_data' => 'array',
            'status' => ImportRowStatus::class,
            'error_details' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }

    public function scopeStatus($query, ImportRowStatus|string $status)
    {
        $value = $status instanceof ImportRowStatus ? $status->value : $status;

        return $query->where('status', $value);
    }

    public function markAsProcessing(): void
    {
        $this->update(['status' => ImportRowStatus::Processing]);
    }

    public function markAsSuccess(): void
    {
        $this->update([
            'status' => ImportRowStatus::Success,
            'processed_at' => now(),
        ]);
    }

    public function markAsFailed(string $message, ?array $details = null): void
    {
        $this->update([
            'status' => ImportRowStatus::Failed,
            'error_message' => $message,
            'error_details' => $details,
            'processed_at' => now(),
        ]);
    }

    public function markAsSkipped(?string $reason = null): void
    {
        $this->update([
            'status' => ImportRowStatus::Skipped,
            'error_message' => $reason,
            'processed_at' => now(),
        ]);
    }
}
