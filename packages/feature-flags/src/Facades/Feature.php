<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Facades;

use Illuminate\Support\Facades\Facade;
use MageTech\FeatureFlags\Services\FeatureFlagService;

/**
 * @method static bool enabled(string $key, mixed $subject = null)
 * @method static bool disabled(string $key, mixed $subject = null)
 * @method static bool active(string $key)
 * @method static \MageTech\FeatureFlags\Services\FeatureFlagService for(mixed $subject)
 * @method static ?string variant(string $key, mixed $subject = null)
 * @method static mixed value(string $key, mixed $subject = null)
 * @method static mixed config(string $key, mixed $subject = null)
 * @method static \Illuminate\Database\Eloquent\Collection getAll()
 * @method static \MageTech\FeatureFlags\Models\FeatureFlag create(array $data)
 * @method static \MageTech\FeatureFlags\Models\FeatureFlag update(\MageTech\FeatureFlags\Models\FeatureFlag $flag, array $data)
 * @method static bool delete(\MageTech\FeatureFlags\Models\FeatureFlag $flag)
 * @method static \MageTech\FeatureFlags\Models\FeatureFlag enable(string $key)
 * @method static \MageTech\FeatureFlags\Models\FeatureFlag disable(string $key)
 * @method static void clearCache(?string $key = null)
 *
 * @see \MageTech\FeatureFlags\Services\FeatureFlagService
 */
class Feature extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FeatureFlagService::class;
    }
}
