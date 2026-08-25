<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeatureFlagOverride extends Model
{
    protected $table = 'mts_feature_flag_overrides';

    protected $fillable = [
        'feature_flag_id',
        'subject_type',
        'subject_id',
        'enabled',
        'variant',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function featureFlag(): BelongsTo
    {
        return $this->belongsTo(FeatureFlag::class, 'feature_flag_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
