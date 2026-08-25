<?php

declare(strict_types=1);

namespace MageTech\Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowLog extends Model
{
    public $timestamps = false;

    protected $table = 'mts_workflow_logs';

    protected $fillable = [
        'instance_id',
        'actor_id',
        'actor_type',
        'action',
        'from_state',
        'to_state',
        'step_name',
        'reason',
        'metadata',
        'request_id',
        'ip_address',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
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

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query, int $limit = 50)
    {
        return $query->orderByDesc('created_at')->limit($limit);
    }
}
