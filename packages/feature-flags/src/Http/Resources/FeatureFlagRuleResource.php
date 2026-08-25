<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeatureFlagRuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'feature_flag_id' => $this->feature_flag_id,
            'rule_type' => $this->rule_type->value,
            'rule_type_label' => $this->rule_type->label(),
            'operator' => $this->operator->value,
            'operator_label' => $this->operator->label(),
            'attribute' => $this->attribute,
            'value' => $this->value,
            'priority' => $this->priority,
            'enabled' => $this->enabled,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
