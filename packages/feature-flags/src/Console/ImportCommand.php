<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use MageTech\FeatureFlags\Enums\FeatureFlagType;
use MageTech\FeatureFlags\Enums\RuleOperator;
use MageTech\FeatureFlags\Enums\RuleType;
use MageTech\FeatureFlags\Models\FeatureFlag;

class ImportCommand extends Command
{
    protected $signature = 'mts:feature-flags:import {file : JSON file to import} {--clear : Clear existing flags before import}';

    protected $description = 'Import feature flags from a JSON file';

    public function handle(): int
    {
        $file = $this->argument('file');

        if (! file_exists($file)) {
            $this->error("File [{$file}] not found.");

            return self::FAILURE;
        }

        $json = file_get_contents($file);
        $data = json_decode($json, true);

        if (! is_array($data)) {
            $this->error('Invalid JSON format.');

            return self::FAILURE;
        }

        if ($this->option('clear')) {
            if ($this->confirm('This will delete all existing feature flags. Continue?', false)) {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
                FeatureFlag::truncate();
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
                $this->info('Existing feature flags cleared.');
            }
        }

        $imported = 0;
        $skipped = 0;

        foreach ($data as $flagData) {
            try {
                $existing = FeatureFlag::where('key', $flagData['key'])->first();

                if ($existing !== null) {
                    $this->line("  Skipping [{$flagData['key']}]: already exists.");
                    $skipped++;

                    continue;
                }

                DB::transaction(function () use ($flagData, &$imported) {
                    $flag = FeatureFlag::create([
                        'uuid' => \Illuminate\Support\Str::uuid()->toString(),
                        'key' => $flagData['key'],
                        'name' => $flagData['name'],
                        'description' => $flagData['description'] ?? null,
                        'type' => $flagData['type'] ?? 'boolean',
                        'enabled' => $flagData['enabled'] ?? false,
                        'environment' => $flagData['environment'] ?? null,
                        'rollout_percentage' => $flagData['rollout_percentage'] ?? 0,
                        'starts_at' => $flagData['starts_at'] ?? null,
                        'ends_at' => $flagData['ends_at'] ?? null,
                        'default_variant' => $flagData['default_variant'] ?? null,
                        'metadata' => $flagData['metadata'] ?? null,
                    ]);

                    foreach ($flagData['rules'] ?? [] as $ruleData) {
                        $flag->rules()->create([
                            'rule_type' => $ruleData['rule_type'],
                            'operator' => $ruleData['operator'] ?? 'equals',
                            'attribute' => $ruleData['attribute'],
                            'value' => $ruleData['value'],
                            'priority' => $ruleData['priority'] ?? 0,
                            'enabled' => $ruleData['enabled'] ?? true,
                        ]);
                    }

                    foreach ($flagData['variants'] ?? [] as $variantData) {
                        $flag->variants()->create([
                            'key' => $variantData['key'],
                            'name' => $variantData['name'],
                            'value' => $variantData['value'] ?? null,
                            'weight' => $variantData['weight'] ?? 1,
                            'enabled' => $variantData['enabled'] ?? true,
                        ]);
                    }

                    foreach ($flagData['environments'] ?? [] as $envData) {
                        $flag->environments()->create([
                            'environment' => $envData['environment'],
                            'enabled' => $envData['enabled'] ?? false,
                            'rollout_percentage' => $envData['rollout_percentage'] ?? 0,
                            'configuration' => $envData['configuration'] ?? null,
                        ]);
                    }

                    $imported++;
                });

                $this->line("  Imported [{$flagData['key']}].");
            } catch (\Exception $e) {
                $this->error("  Failed to import [{$flagData['key']}]: {$e->getMessage()}");
                $skipped++;
            }
        }

        $this->info("Import complete: {$imported} imported, {$skipped} skipped.");

        return self::SUCCESS;
    }
}
