<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Console;

use Illuminate\Console\Command;
use MageTech\FeatureFlags\Services\FeatureFlagService;

class EnableCommand extends Command
{
    protected $signature = 'mts:feature-flags:enable {key : The feature flag key}';

    protected $description = 'Enable a feature flag';

    public function handle(FeatureFlagService $service): int
    {
        $key = $this->argument('key');

        try {
            $flag = $service->enable($key);
            $this->info("Feature flag [{$flag->key}] has been enabled.");

            return self::SUCCESS;
        } catch (\MageTech\FeatureFlags\Exceptions\FeatureFlagNotFoundException $e) {
            $this->error("Feature flag [{$key}] not found.");

            return self::FAILURE;
        }
    }
}
