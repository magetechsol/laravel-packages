<?php

declare(strict_types=1);

namespace MageTech\Audit\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use MageTech\Audit\Models\Audit;

class CleanupCommand extends Command
{
    protected $signature = 'audit:cleanup
                    {--days= : Number of days to retain (default: config)}
                    {--dry-run : Only show what would be deleted}
                    {--batch=1000 : Number of records to delete at once}';

    protected $description = 'Clean up old audit records based on retention policy';

    public function handle(): int
    {
        $this->info('MTS Laravel Audit Pro - Cleanup');
        $this->newLine();

        $retentionDays = $this->option('days') ?? config('audit.retention.days', 365);
        $dryRun = $this->option('dry-run');
        $batchSize = (int) $this->option('batch');

        $cutoffDate = now()->subDays($retentionDays);

        $this->info("Retention policy: {$retentionDays} days");
        $this->info("Cutoff date: {$cutoffDate->format('Y-m-d H:i:s')}");
        $this->newLine();

        $query = Audit::query()->where('created_at', '<', $cutoffDate);
        $totalRecords = $query->count();

        $this->info("Records to delete: {$totalRecords}");

        if ($totalRecords === 0) {
            $this->info('No records to clean up.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->newLine();
            $this->warn('DRY RUN - No records will be deleted.');
            $this->newLine();

            $this->line('Sample records that would be deleted:');

            Audit::query()
                ->where('created_at', '<', $cutoffDate)
                ->orderBy('created_at')
                ->limit(10)
                ->get()
                ->each(function ($record) {
                    $this->line("  [{$record->uuid}] {$record->event} - {$record->created_at}");
                });

            if ($totalRecords > 10) {
                $this->line("  ... and " . ($totalRecords - 10) . " more");
            }

            return self::SUCCESS;
        }

        if (!$this->confirm("Are you sure you want to delete {$totalRecords} audit records?", false)) {
            $this->info('Cleanup cancelled.');

            return self::SUCCESS;
        }

        $deleted = 0;
        $progress = $this->output->createProgressBar($totalRecords);
        $progress->start();

        DB::beginTransaction();

        try {
            while ($deleted < $totalRecords) {
                $batchDeleted = Audit::query()
                    ->where('created_at', '<', $cutoffDate)
                    ->limit($batchSize)
                    ->delete();

                if ($batchDeleted === 0) {
                    break;
                }

                $deleted += $batchDeleted;
                $progress->advance($batchDeleted);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Cleanup failed: {$e->getMessage()}");

            return self::FAILURE;
        }

        $progress->finish();
        $this->newLine(2);

        $this->info("Cleanup complete! {$deleted} records deleted.");

        return self::SUCCESS;
    }
}
