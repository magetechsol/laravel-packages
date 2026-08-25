<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Console;

use Illuminate\Console\Command;
use MageTech\FeatureFlags\Services\FeatureFlagService;

class CreateCommand extends Command
{
    protected $signature = 'mts:feature-flags:create';

    protected $description = 'Create a new feature flag interactively';

    public function handle(FeatureFlagService $service): int
    {
        $key = $this->ask('Feature flag key (lowercase, dashes, dots)', function (string $value) {
            if (! preg_match('/^[a-z0-9._-]+$/', $value)) {
                throw new \InvalidArgumentException('Invalid key format. Use lowercase letters, numbers, dots, dashes, and underscores only.');
            }

            return $value;
        });

        $name = $this->ask('Display name');

        $description = $this->ask('Description (optional)');

        $type = $this->choice('Type', ['boolean', 'percentage', 'variant', 'config'], 0);

        $environment = $this->ask('Environment (leave empty for all)', null);

        $rolloutPercentage = 0;

        if ($type === 'percentage') {
            $rolloutPercentage = (int) $this->ask('Rollout percentage (0-100)', 0);
        }

        $enabled = $this->confirm('Enable immediately?', false);

        $flag = $service->create([
            'key' => $key,
            'name' => $name,
            'description' => $description,
            'type' => $type,
            'environment' => $environment ?: null,
            'rollout_percentage' => $rolloutPercentage,
            'enabled' => $enabled,
        ]);

        $this->info("Feature flag [{$flag->key}] created successfully.");

        return self::SUCCESS;
    }
}
