<?php

declare(strict_types=1);

namespace MageTech\Workflow\Audit;

use Illuminate\Support\Facades\Request;
use MageTech\Workflow\Enums\TransitionType;
use MageTech\Workflow\Models\WorkflowInstance;
use MageTech\Workflow\Models\WorkflowLog;
use MageTech\Workflow\Models\WorkflowTransition;

class AuditLogger
{
    public function log(
        WorkflowInstance $instance,
        TransitionType $type,
        ?string $stepName = null,
        ?string $fromState = null,
        ?string $toState = null,
        ?int $actorId = null,
        ?string $actorType = null,
        ?string $reason = null,
        ?array $metadata = null,
    ): void {
        if (! config('mts-workflow.audit.enabled', true)) {
            return;
        }

        WorkflowLog::create([
            'instance_id' => $instance->id,
            'actor_id' => $actorId,
            'actor_type' => $actorType,
            'action' => $type->value,
            'from_state' => $fromState,
            'to_state' => $toState,
            'step_name' => $stepName,
            'reason' => $reason,
            'metadata' => $metadata,
            'request_id' => config('mts-workflow.audit.log_request_id', true) ? Request::header('X-Request-ID') : null,
            'ip_address' => config('mts-workflow.audit.log_ip_address', true) ? Request::ip() : null,
            'created_at' => now(),
        ]);

        WorkflowTransition::create([
            'instance_id' => $instance->id,
            'step_name' => $stepName,
            'type' => $type,
            'from_state' => $fromState,
            'to_state' => $toState,
            'actor_id' => $actorId,
            'actor_type' => $actorType,
            'reason' => $reason,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }

    /**
     * Get audit logs for a workflow instance.
     */
    public function getLogs(WorkflowInstance $instance, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return WorkflowLog::where('instance_id', $instance->id)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
