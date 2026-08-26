<?php

declare(strict_types=1);

namespace MageTech\Audit\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use MageTech\Audit\Models\Audit;

class PruneCommand extends Command
{
    protected $signature = 'audit:prune
                    {--before= : Delete records before this date (Y-m-d)}
                    {--days= : Delete records older than N days}
                    {--dry-run : Only show what would be deleted}
                    {--batch=1000 : Number of records to delete at once}';

    protected $description = 'Prune audit records by date';

    public function handle(): int
    {
        $this->info('MTS Laravel Audit Pro - Prune');
        $this->newLine();

        $before = $this->option('before');
        $days = $this->option('days');
        $dryRun = $this->option('dry-run');
        $batchSize = (int) $this->option('batch');

        if (!$before && !$days) {
            $this->error('Please specify either --before or --days option.');

            return self::FAILURE;
        }

        if ($before) {
            $cutoffDate = now()->parse($before);
        } else {
            $cutoffDate = now()->subDays((int) $days);
        }

        $this->info("Pruning records before: {$cutoffDate->format('Y-m-d H:i:s')}");
        $this->newLine();

        $totalRecords = Audit::query()->where('created_at', '<', $cutoffDate)->count();

        $this->info("Records to prune: {$totalRecords}");

        if ($totalRecords === 0) {
            $this->info('No records to prune.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->newLine();
            $this->warn('DRY RUN - No records will be deleted.');
            $this->newLine();

            $this->line('Sample records that would be pruned:');

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

        if (!$this->confirm("Are you sure you want to prune {$totalRecords} audit records?", false)) {
            $this->info('Prune cancelled.');

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
            $this->error("Prune failed: {$e->getMessage()}");

            return self::FAILURE;
        }

        $progress->finish();
        $this->newLine(2);

        $this->info("Prune complete! {$deleted} records removed.");

        return self::SUCCESS;
    }
}
