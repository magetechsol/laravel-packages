<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MageTech\FeatureFlags\Enums\FeatureFlagType;

class FeatureFlag extends Model
{
    protected $table = 'mts_feature_flags';

    protected $fillable = [
        'uuid',
        'key',
        'name',
        'description',
        'type',
        'enabled',
        'environment',
        'rollout_percentage',
        'starts_at',
        'ends_at',
        'default_variant',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'rollout_percentage' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'metadata' => 'array',
            'type' => FeatureFlagType::class,
        ];
    }

    public function rules(): HasMany
    {
        return $this->hasMany(FeatureFlagRule::class, 'feature_flag_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(FeatureFlagVariant::class, 'feature_flag_id');
    }

    public function overrides(): HasMany
    {
        return $this->hasMany(FeatureFlagOverride::class, 'feature_flag_id');
    }

    public function environments(): HasMany
    {
        return $this->hasMany(FeatureFlagEnvironment::class, 'feature_flag_id');
    }

    public function scopeForEnvironment($query, string $environment)
    {
        return $query->where(function ($q) use ($environment) {
            $q->where('environment', $environment)
                ->orWhereNull('environment');
        });
    }

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    public function scopeActive($query)
    {
        $now = now();

        return $query->where('enabled', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $now);
            });
    }

    public function isCurrentlyActive(): bool
    {
        if (! $this->enabled) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $this->starts_at->isAfter($now)) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isBefore($now)) {
            return false;
        }

        return true;
    }

    public function isScheduled(): bool
    {
        return $this->starts_at !== null || $this->ends_at !== null;
    }

    public function hasStarted(): bool
    {
        return $this->starts_at === null || $this->starts_at->isPast();
    }

    public function hasEnded(): bool
    {
        return $this->ends_at !== null && $this->ends_at->isPast();
    }
}
