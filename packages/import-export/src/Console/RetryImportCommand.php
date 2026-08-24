<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Console;

use Illuminate\Console\Command;
use MageTech\ImportExport\Enums\ImportStatus;
use MageTech\ImportExport\Jobs\RetryImportJob;
use MageTech\ImportExport\Models\Import;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'mts:import:retry', description: 'Retry failed rows for an import')]
class RetryImportCommand extends Command
{
    protected $signature = 'mts:import:retry {id : The import ID to retry} {--queue : Dispatch retry to queue}';

    protected $description = 'Retry failed rows for a specific import';

    public function handle(): int
    {
        $id = (int) $this->argument('id');

        $import = Import::find($id);

        if ($import === null) {
            $this->error("Import #{$id} not found.");

            return Command::FAILURE;
        }

        if (! $import->status->canRetry()) {
            $this->error("Import #{$id} cannot be retried (status: {$import->status->value}).");

            return Command::FAILURE;
        }

        $failedCount = $import->failedRows()->count();

        if ($failedCount === 0) {
            $this->info("Import #{$id} has no failed rows to retry.");

            return Command::SUCCESS;
        }

        $this->info("Retrying {$failedCount} failed row(s) for import #{$id}...");

        if ($this->option('queue')) {
            RetryImportJob::dispatch($import);
            $this->info('Retry job dispatched to queue.');
        } else {
            try {
                $import->update(['status' => ImportStatus::Processing]);
                RetryImportJob::dispatchSync($import);
                $this->info("Import #{$id} retry completed.");
            } catch (\Throwable $e) {
                $this->error("Import #{$id} retry failed: {$e->getMessage()}");

                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }
}
