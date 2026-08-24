<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportError extends Model
{
    protected $fillable = [
        'import_id',
        'import_row_id',
        'row_number',
        'column',
        'value',
        'error',
        'error_code',
        'context',
    ];

    protected function casts(): array
    {
        return [
            'row_number' => 'integer',
            'context' => 'array',
        ];
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }

    public function row(): BelongsTo
    {
        return $this->belongsTo(ImportRow::class, 'import_row_id');
    }

    public function scopeForImport($query, int $importId)
    {
        return $query->where('import_id', $importId);
    }

    public function scopeForColumn($query, string $column)
    {
        return $query->where('column', $column);
    }
}
