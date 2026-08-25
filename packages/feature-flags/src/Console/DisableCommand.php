<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Console;

use Illuminate\Console\Command;
use MageTech\FeatureFlags\Services\FeatureFlagService;

class DisableCommand extends Command
{
    protected $signature = 'mts:feature-flags:disable {key : The feature flag key}';

    protected $description = 'Disable a feature flag';

    public function handle(FeatureFlagService $service): int
    {
        $key = $this->argument('key');

        try {
            $flag = $service->disable($key);
            $this->info("Feature flag [{$flag->key}] has been disabled.");

            return self::SUCCESS;
        } catch (\MageTech\FeatureFlags\Exceptions\FeatureFlagNotFoundException $e) {
            $this->error("Feature flag [{$key}] not found.");

            return self::FAILURE;
        }
    }
}
