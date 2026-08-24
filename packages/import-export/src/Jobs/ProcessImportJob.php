<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MageTech\ImportExport\Importers\Importer;
use MageTech\ImportExport\Mappers\ColumnMapper;
use MageTech\ImportExport\Models\Import;
use MageTech\ImportExport\Transformers\TransformerPipeline;
use MageTech\ImportExport\Validators\DuplicateDetector;
use MageTech\ImportExport\Validators\FileValidator;
use MageTech\ImportExport\Validators\RowValidator;

class ProcessImportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 600;

    public int $tries = 3;

    public int $maxExceptions = 10;

    public function __construct(
        public Import $import,
    ) {
        $this->queue = config('mts-import-export.import.queue_name', 'imports');
        $this->connection = config('mts-import-export.import.queue_connection', 'redis');
        $this->timeout = config('mts-import-export.import.timeout', 600);
        $this->tries = config('mts-import-export.import.tries', 3);
        $this->maxExceptions = config('mts-import-export.import.max_exceptions', 10);
    }

    public function handle(): void
    {
        $importer = $this->buildImporter();
        $importer->process($this->import);
    }

    public function failed(\Throwable $exception): void
    {
        $this->import->markAsFailed($exception->getMessage());
    }

    private function buildImporter(): Importer
    {
        $mapping = $this->import->mapping ?? [];
        $options = $this->import->options ?? [];

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
            modelClass: $this->import->options['model_class'] ?? throw new \RuntimeException('Model class not set'),
        );

        $transformerPipeline = new TransformerPipeline;

        if (($options['transform_types'] ?? []) !== []) {
            $transformerPipeline->withTypeCaster($options['transform_types']);
        }

        $fileValidator = new FileValidator;

        return new Importer(
            modelClass: $this->import->options['model_class'],
            mapper: $mapper,
            rowValidator: $rowValidator,
            duplicateDetector: $duplicateDetector,
            transformerPipeline: $transformerPipeline,
            fileValidator: $fileValidator,
            chunkSize: $this->import->options['chunk_size'] ?? config('mts-import-export.import.chunk_size', 1000),
        );
    }
}
