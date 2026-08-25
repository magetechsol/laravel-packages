<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Console;

use Illuminate\Console\Command;
use MageTech\FeatureFlags\Models\FeatureFlag;

class ListCommand extends Command
{
    protected $signature = 'mts:feature-flags:list {--environment= : Filter by environment} {--json : Output as JSON}';

    protected $description = 'List all feature flags';

    public function handle(): int
    {
        $query = FeatureFlag::query();

        if ($environment = $this->option('environment')) {
            $query->forEnvironment($environment);
        }

        $flags = $query->get();

        if ($flags->isEmpty()) {
            $this->info('No feature flags found.');

            return self::SUCCESS;
        }

        if ($this->option('json')) {
            $this->line($flags->toJson());

            return self::SUCCESS;
        }

        $rows = $flags->map(fn ($flag) => [
            $flag->key,
            $flag->name,
            $flag->type->value,
            $flag->enabled ? 'Yes' : 'No',
            $flag->environment ?? 'All',
            $flag->rollout_percentage . '%',
            $flag->starts_at?->format('Y-m-d H:i') ?? '-',
            $flag->ends_at?->format('Y-m-d H:i') ?? '-',
        ])->toArray();

        $this->table(
            ['Key', 'Name', 'Type', 'Enabled', 'Environment', 'Rollout', 'Starts', 'Ends'],
            $rows
        );

        $this->info("Total: {$flags->count()} feature flags.");

        return self::SUCCESS;
    }
}
