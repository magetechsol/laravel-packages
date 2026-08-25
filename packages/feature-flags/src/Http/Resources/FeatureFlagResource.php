<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeatureFlagResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'key' => $this->key,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'enabled' => $this->enabled,
            'environment' => $this->environment,
            'rollout_percentage' => $this->rollout_percentage,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'default_variant' => $this->default_variant,
            'metadata' => $this->metadata,
            'is_active' => $this->isCurrentlyActive(),
            'is_scheduled' => $this->isScheduled(),
            'rules_count' => $this->whenCounted('rules'),
            'variants_count' => $this->whenCounted('variants'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
