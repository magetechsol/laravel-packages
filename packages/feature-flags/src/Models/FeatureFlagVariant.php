<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeatureFlagVariant extends Model
{
    protected $table = 'mts_feature_flag_variants';

    protected $fillable = [
        'feature_flag_id',
        'key',
        'name',
        'value',
        'weight',
        'enabled',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
            'weight' => 'integer',
            'enabled' => 'boolean',
        ];
    }

    public function featureFlag(): BelongsTo
    {
        return $this->belongsTo(FeatureFlag::class, 'feature_flag_id');
    }

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }
}
