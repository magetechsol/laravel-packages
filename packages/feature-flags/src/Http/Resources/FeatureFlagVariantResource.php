<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeatureFlagVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'feature_flag_id' => $this->feature_flag_id,
            'key' => $this->key,
            'name' => $this->name,
            'value' => $this->value,
            'weight' => $this->weight,
            'enabled' => $this->enabled,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
