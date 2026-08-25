<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Console;

use Illuminate\Console\Command;
use MageTech\FeatureFlags\Services\FeatureFlagService;

class ClearCacheCommand extends Command
{
    protected $signature = 'mts:feature-flags:clear-cache {key? : Specific flag key to clear}';

    protected $description = 'Clear the feature flags cache';

    public function handle(FeatureFlagService $service): int
    {
        $key = $this->argument('key');

        $service->clearCache($key);

        if ($key !== null) {
            $this->info("Cache cleared for feature flag [{$key}].");
        } else {
            $this->info('All feature flags cache cleared.');
        }

        return self::SUCCESS;
    }
}
