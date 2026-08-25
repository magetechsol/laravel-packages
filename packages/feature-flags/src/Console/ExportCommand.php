<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Console;

use Illuminate\Console\Command;
use MageTech\FeatureFlags\Models\FeatureFlag;

class ExportCommand extends Command
{
    protected $signature = 'mts:feature-flags:export {--output= : Output file path} {--environment= : Filter by environment}';

    protected $description = 'Export all feature flags to JSON';

    public function handle(): int
    {
        $query = FeatureFlag::query();

        if ($environment = $this->option('environment')) {
            $query->forEnvironment($environment);
        }

        $flags = $query->with(['rules', 'variants', 'environments'])->get();

        $data = $flags->map(fn ($flag) => [
            'key' => $flag->key,
            'name' => $flag->name,
            'description' => $flag->description,
            'type' => $flag->type->value,
            'enabled' => $flag->enabled,
            'environment' => $flag->environment,
            'rollout_percentage' => $flag->rollout_percentage,
            'starts_at' => $flag->starts_at?->toISOString(),
            'ends_at' => $flag->ends_at?->toISOString(),
            'default_variant' => $flag->default_variant,
            'metadata' => $flag->metadata,
            'rules' => $flag->rules->map(fn ($rule) => [
                'rule_type' => $rule->rule_type->value,
                'operator' => $rule->operator->value,
                'attribute' => $rule->attribute,
                'value' => $rule->value,
                'priority' => $rule->priority,
                'enabled' => $rule->enabled,
            ])->toArray(),
            'variants' => $flag->variants->map(fn ($variant) => [
                'key' => $variant->key,
                'name' => $variant->name,
                'value' => $variant->value,
                'weight' => $variant->weight,
                'enabled' => $variant->enabled,
            ])->toArray(),
            'environments' => $flag->environments->map(fn ($env) => [
                'environment' => $env->environment,
                'enabled' => $env->enabled,
                'rollout_percentage' => $env->rollout_percentage,
                'configuration' => $env->configuration,
            ])->toArray(),
        ])->toArray();

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $outputPath = $this->option('output');

        if ($outputPath !== null) {
            file_put_contents($outputPath, $json);
            $this->info("Exported {$flags->count()} feature flags to {$outputPath}.");
        } else {
            $this->line($json);
        }

        return self::SUCCESS;
    }
}
