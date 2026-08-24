<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Console;

use Illuminate\Console\Command;
use MageTech\ImportExport\Models\Import;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'mts:import:cancel', description: 'Cancel a running import')]
class CancelImportCommand extends Command
{
    protected $signature = 'mts:import:cancel {id : The import ID to cancel}';

    protected $description = 'Cancel a running import';

    public function handle(): int
    {
        $id = (int) $this->argument('id');

        $import = Import::find($id);

        if ($import === null) {
            $this->error("Import #{$id} not found.");

            return Command::FAILURE;
        }

        if (! $import->canCancel()) {
            $this->error("Import #{$id} cannot be cancelled (status: {$import->status->value}).");

            return Command::FAILURE;
        }

        $import->markAsCancelled();
        $this->info("Import #{$id} has been cancelled.");

        return Command::SUCCESS;
    }
}
