<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Console;

use Illuminate\Console\Command;
use MageTech\FeatureFlags\Models\FeatureFlag;
use MageTech\FeatureFlags\Services\FeatureFlagService;

class CheckCommand extends Command
{
    protected $signature = 'mts:feature-flags:check {key : The feature flag key} {--user= : User ID to check for}';

    protected $description = 'Check the status of a feature flag';

    public function handle(FeatureFlagService $service): int
    {
        $key = $this->argument('key');
        $userId = $this->option('user');

        $environment = app(\MageTech\FeatureFlags\Support\EnvironmentResolver::class)->resolve();

        $flag = FeatureFlag::where('key', $key)
            ->where(function ($q) use ($environment) {
                $q->where('environment', $environment)
                    ->orWhereNull('environment');
            })
            ->first();

        if ($flag === null) {
            $this->error("Feature flag [{$key}] not found.");

            return self::FAILURE;
        }

        $subject = $userId ? (int) $userId : null;
        $enabled = $service->for($subject)->enabled($key);
        $variant = $service->for($subject)->variant($key);
        $value = $service->for($subject)->value($key);

        $this->table(
            ['Property', 'Value'],
            [
                ['Key', $flag->key],
                ['Name', $flag->name],
                ['Type', $flag->type->label()],
                ['Environment', $flag->environment ?? 'All'],
                ['Globally Enabled', $flag->enabled ? 'Yes' : 'No'],
                ['Currently Active', $flag->isCurrentlyActive() ? 'Yes' : 'No'],
                ['Rollout %', $flag->rollout_percentage . '%'],
                ['Resolved Enabled', $enabled ? 'Yes' : 'No'],
                ['Variant', $variant ?? '-'],
                ['Value', is_array($value) ? json_encode($value) : ($value ?? '-')],
                ['Starts At', $flag->starts_at?->format('Y-m-d H:i') ?? '-'],
                ['Ends At', $flag->ends_at?->format('Y-m-d H:i') ?? '-'],
            ]
        );

        return self::SUCCESS;
    }
}
