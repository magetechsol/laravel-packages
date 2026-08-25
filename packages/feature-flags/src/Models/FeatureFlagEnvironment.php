<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeatureFlagEnvironment extends Model
{
    protected $table = 'mts_feature_flag_environments';

    protected $fillable = [
        'feature_flag_id',
        'environment',
        'enabled',
        'rollout_percentage',
        'configuration',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'rollout_percentage' => 'integer',
            'configuration' => 'array',
        ];
    }

    public function featureFlag(): BelongsTo
    {
        return $this->belongsTo(FeatureFlag::class, 'feature_flag_id');
    }
}
