<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Console;

use Illuminate\Console\Command;
use MageTech\ImportExport\Enums\ImportStatus;
use MageTech\ImportExport\Importers\Importer;
use MageTech\ImportExport\Mappers\ColumnMapper;
use MageTech\ImportExport\Models\Import;
use MageTech\ImportExport\Transformers\TransformerPipeline;
use MageTech\ImportExport\Validators\DuplicateDetector;
use MageTech\ImportExport\Validators\FileValidator;
use MageTech\ImportExport\Validators\RowValidator;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'mts:import:process', description: 'Process a pending import')]
class ProcessImportCommand extends Command
{
    protected $signature = 'mts:import:process {--id= : The import ID to process}';

    protected $description = 'Process pending imports or a specific import by ID';

    public function handle(): int
    {
        $id = $this->option('id');

        if ($id !== null) {
            return $this->processImport((int) $id);
        }

        return $this->processPending();
    }

    private function processImport(int $id): int
    {
        $import = Import::find($id);

        if ($import === null) {
            $this->error("Import #{$id} not found.");

            return Command::FAILURE;
        }

        if (! $import->status->canRetry() && $import->status !== ImportStatus::Pending) {
            $this->error("Import #{$id} cannot be processed (status: {$import->status->value}).");

            return Command::FAILURE;
        }

        $this->info("Processing import #{$id}...");

        try {
            $importer = $this->buildImporter($import);
            $importer->process($import);
            $this->info("Import #{$id} completed successfully.");
        } catch (\Throwable $e) {
            $this->error("Import #{$id} failed: {$e->getMessage()}");

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function processPending(): int
    {
        $imports = Import::status(ImportStatus::Pending)->get();

        if ($imports->isEmpty()) {
            $this->info('No pending imports found.');

            return Command::SUCCESS;
        }

        $this->info("Found {$imports->count()} pending import(s).");

        foreach ($imports as $import) {
            $this->line("Processing import #{$import->id}: {$import->name}");

            try {
                $importer = $this->buildImporter($import);
                $importer->process($import);
                $this->info("  Import #{$import->id} completed.");
            } catch (\Throwable $e) {
                $this->error("  Import #{$import->id} failed: {$e->getMessage()}");
            }
        }

        return Command::SUCCESS;
    }

    private function buildImporter(Import $import): Importer
    {
        $mapping = $import->mapping ?? [];
        $options = $import->options ?? [];

        $mapper = new ColumnMapper(
            mapping: $mapping,
            defaults: $options['defaults'] ?? [],
            skipColumns: $options['skip_columns'] ?? [],
        );

        $rowValidator = new RowValidator(
            rules: $options['validation_rules'] ?? [],
        );

        $duplicateDetector = new DuplicateDetector(
            mode: $options['duplicate_mode'] ?? 'ignore',
            uniqueKey: $options['duplicate_key'] ?? null,
            modelClass: $options['model_class'] ?? throw new \RuntimeException('Model class not set in import options'),
        );

        $transformerPipeline = new TransformerPipeline;

        if (($options['transform_types'] ?? []) !== []) {
            $transformerPipeline->withTypeCaster($options['transform_types']);
        }

        return new Importer(
            modelClass: $options['model_class'],
            mapper: $mapper,
            rowValidator: $rowValidator,
            duplicateDetector: $duplicateDetector,
            transformerPipeline: $transformerPipeline,
            fileValidator: new FileValidator,
            chunkSize: $options['chunk_size'] ?? config('mts-import-export.import.chunk_size', 1000),
        );
    }
}
