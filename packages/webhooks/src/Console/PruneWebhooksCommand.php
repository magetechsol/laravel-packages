<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneWebhooksCommand extends Command
{
    protected $signature = 'mts:webhook:prune
                    {--days= : Number of days to retain (overrides config)}
                    {--dry-run : Show what would be pruned without deleting}';

    protected $description = 'Prune old webhook records based on retention policy';

    public function handle(): int
    {
        $retainDays = (int) ($this->option('days') ?? config('mts-webhooks.pruning.retain_days', 90));

        $cutoffDate = now()->subDays($retainDays);

        $webhookCount = DB::table('mts_webhooks')
            ->where('created_at', '<', $cutoffDate)
            ->count();

        $attemptCount = DB::table('mts_webhook_attempts')
            ->where('created_at', '<', $cutoffDate)
            ->count();

        $deliveryCount = DB::table('mts_webhook_deliveries')
            ->where('created_at', '<', $cutoffDate)
            ->count();

        if ($webhookCount === 0 && $attemptCount === 0 && $deliveryCount === 0) {
            $this->info('No records to prune (older than ' . $retainDays . ' days).');

            return self::SUCCESS;
        }

        $this->info('Records to prune (older than ' . $retainDays . ' days):');
        $this->line('  Webhooks: ' . $webhookCount);
        $this->line('  Attempts: ' . $attemptCount);
        $this->line('  Deliveries: ' . $deliveryCount);

        if ($this->option('dry-run')) {
            $this->info('Dry run - no records deleted.');

            return self::SUCCESS;
        }

        if (! $this->confirm('Proceed with pruning?')) {
            $this->info('Pruning cancelled.');

            return self::SUCCESS;
        }

        DB::table('mts_webhook_attempts')
            ->where('created_at', '<', $cutoffDate)
            ->delete();

        DB::table('mts_webhooks')
            ->where('created_at', '<', $cutoffDate)
            ->delete();

        DB::table('mts_webhook_deliveries')
            ->where('created_at', '<', $cutoffDate)
            ->delete();

        $this->info('Pruning completed successfully.');

        return self::SUCCESS;
    }
}
