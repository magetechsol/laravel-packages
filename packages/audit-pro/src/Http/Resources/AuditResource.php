<?php

declare(strict_types=1);

namespace MageTech\Audit\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'event' => $this->event,
            'auditable_type' => $this->auditable_type,
            'auditable_id' => $this->auditable_id,
            'actor_type' => $this->actor_type,
            'actor_id' => $this->actor_id,
            'actor_name' => $this->actor_name,
            'actor_email' => $this->actor_email,
            'action' => $this->action,
            'description' => $this->description,
            'url' => $this->url,
            'method' => $this->method,
            'route' => $this->route,
            'ip_address' => $this->ip_address,
            'old_values' => $this->old_values,
            'new_values' => $this->new_values,
            'changed_values' => $this->changed_values,
            'metadata' => $this->metadata,
            'tags' => $this->tags,
            'tenant_id' => $this->tenant_id,
            'batch_uuid' => $this->batch_uuid,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
