<?php

declare(strict_types=1);

namespace MageTech\Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MageTech\Workflow\Enums\WorkflowStatus;

class Workflow extends Model
{
    protected $table = 'mts_workflows';

    protected $fillable = [
        'name',
        'description',
        'definition',
        'version',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'definition' => 'array',
            'is_active' => 'boolean',
            'version' => 'integer',
        ];
    }

    public function instances(): HasMany
    {
        return $this->hasMany(WorkflowInstance::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByName($query, string $name)
    {
        return $query->where('name', $name);
    }
}
