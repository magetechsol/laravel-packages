<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MageTech\ImportExport\Enums\ImportRowStatus;
use MageTech\ImportExport\Enums\ImportStatus;
use MageTech\ImportExport\Mappers\ColumnMapper;
use MageTech\ImportExport\Models\Import;
use MageTech\ImportExport\Models\ImportError;
use MageTech\ImportExport\Transformers\TransformerPipeline;
use MageTech\ImportExport\Validators\DuplicateDetector;
use MageTech\ImportExport\Validators\RowValidator;

class RetryImportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 600;

    public int $tries = 3;

    public function __construct(
        public Import $import,
    ) {
        $this->queue = config('mts-import-export.import.queue_name', 'imports');
        $this->connection = config('mts-import-export.import.queue_connection', 'redis');
    }

    public function handle(): void
    {
        $failedRows = $this->import->rows()
            ->where('status', ImportRowStatus::Failed)
            ->get();

        if ($failedRows->isEmpty()) {
            return;
        }

        $this->import->update(['status' => ImportStatus::Processing]);

        $options = $this->import->options ?? [];
        $mapping = $this->import->mapping ?? [];

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
            modelClass: $options['model_class'] ?? '',
        );

        $transformerPipeline = new TransformerPipeline;

        if (($options['transform_types'] ?? []) !== []) {
            $transformerPipeline->withTypeCaster($options['transform_types']);
        }

        $successful = 0;
        $failed = 0;

        foreach ($failedRows as $row) {
            $row->update([
                'status' => ImportRowStatus::Pending,
                'error_message' => null,
                'error_details' => null,
                'processed_at' => null,
            ]);

            // Clear previous errors for this row
            ImportError::where('import_row_id', $row->id)->delete();

            try {
                $rawData = $row->data;
                $mappedData = $mapper->map($rawData);

                // Validate
                if ($rowValidator->getRules() !== []) {
                    $errors = $rowValidator->validate($mappedData);

                    if ($errors !== []) {
                        foreach ($errors as $column => $columnErrors) {
                            foreach ($columnErrors as $error) {
                                ImportError::create([
                                    'import_id' => $this->import->id,
                                    'import_row_id' => $row->id,
                                    'row_number' => $row->row_number,
                                    'column' => $column,
                                    'error' => $error,
                                ]);
                            }
                        }

                        $row->markAsFailed('Validation failed');
                        $failed++;

                        continue;
                    }
                }

                // Transform
                $transformedData = $transformerPipeline->transform($mappedData);

                // Create or update
                $modelClass = $options['model_class'];

                if ($duplicateDetector->shouldUpsert() && $duplicateDetector->isDuplicate($mappedData)) {
                    $key = $duplicateDetector->getUniqueKey() ?? 'id';
                    $keyValue = $transformedData[$key] ?? null;

                    if ($keyValue !== null) {
                        $modelClass::updateOrCreate([$key => $keyValue], $transformedData);
                    } else {
                        $modelClass::create($transformedData);
                    }
                } else {
                    $modelClass::create($transformedData);
                }

                $row->update([
                    'mapped_data' => $transformedData,
                    'status' => ImportRowStatus::Success,
                    'processed_at' => now(),
                ]);

                $successful++;
            } catch (\Throwable $e) {
                $row->markAsFailed($e->getMessage());

                ImportError::create([
                    'import_id' => $this->import->id,
                    'import_row_id' => $row->id,
                    'row_number' => $row->row_number,
                    'error' => $e->getMessage(),
                ]);

                $failed++;
            }
        }

        $this->import->update([
            'successful_rows' => $this->import->successful_rows + $successful,
            'failed_rows' => $failed,
            'status' => $failed > 0 ? ImportStatus::Failed : ImportStatus::Completed,
            'completed_at' => now(),
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        $this->import->markAsFailed('Retry failed: '.$exception->getMessage());
    }
}
