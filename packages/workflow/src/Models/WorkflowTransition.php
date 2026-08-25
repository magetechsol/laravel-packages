<?php

declare(strict_types=1);

namespace MageTech\Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MageTech\Workflow\Enums\TransitionType;

class WorkflowTransition extends Model
{
    public $timestamps = false;

    protected $table = 'mts_workflow_transitions';

    protected $fillable = [
        'instance_id',
        'step_name',
        'type',
        'from_state',
        'to_state',
        'actor_id',
        'actor_type',
        'reason',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => TransitionType::class,
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function instance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'instance_id');
    }

    public function scopeByInstance($query, int $instanceId)
    {
        return $query->where('instance_id', $instanceId);
    }

    public function scopeByType($query, TransitionType|string $type)
    {
        $value = $type instanceof TransitionType ? $type->value : $type;

        return $query->where('type', $value);
    }

    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderByDesc('created_at')->limit($limit);
    }
}
