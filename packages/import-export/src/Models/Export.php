<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Models;

use Illuminate\Database\Eloquent\Model;

class Export extends Model
{
    protected $fillable = [
        'name',
        'model_class',
        'file_type',
        'file_path',
        'file_name',
        'status',
        'total_rows',
        'processed_rows',
        'columns',
        'filters',
        'options',
        'created_by',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'total_rows' => 'integer',
            'processed_rows' => 'integer',
            'columns' => 'array',
            'filters' => 'array',
            'options' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['pending', 'queued', 'processing']);
    }

    public function markAsProcessing(): void
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed(string $message = ''): void
    {
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
        ]);
    }
}
