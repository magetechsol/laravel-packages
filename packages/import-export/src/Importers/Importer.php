<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Importers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use MageTech\ImportExport\Enums\ImportRowStatus;
use MageTech\ImportExport\Enums\ImportStatus;
use MageTech\ImportExport\Events\ImportCancelled;
use MageTech\ImportExport\Events\ImportCompleted;
use MageTech\ImportExport\Events\ImportFailed;
use MageTech\ImportExport\Events\ImportProgress;
use MageTech\ImportExport\Events\ImportStarted;
use MageTech\ImportExport\Exceptions\ImportCancelledException;
use MageTech\ImportExport\Mappers\ColumnMapper;
use MageTech\ImportExport\Models\Import as ImportModel;
use MageTech\ImportExport\Models\ImportError;
use MageTech\ImportExport\Models\ImportRow;
use MageTech\ImportExport\Readers\ReaderFactory;
use MageTech\ImportExport\Transformers\TransformerPipeline;
use MageTech\ImportExport\Validators\DuplicateDetector;
use MageTech\ImportExport\Validators\FileValidator;
use MageTech\ImportExport\Validators\RowValidator;

final class Importer
{
    public function __construct(
        private string $modelClass,
        private ColumnMapper $mapper,
        private RowValidator $rowValidator,
        private DuplicateDetector $duplicateDetector,
        private TransformerPipeline $transformerPipeline,
        private FileValidator $fileValidator,
        private int $chunkSize = 1000,
    ) {
    }

    public function process(ImportModel $import): void
    {
        $import->markAsProcessing();
        Event::dispatch(new ImportStarted($import));

        try {
            $reader = ReaderFactory::make($import->file_path);
            $reader->open($import->file_path);

            $totalRows = $reader->totalRows();
            $import->update(['total_rows' => $totalRows]);

            $processed = 0;
            $successful = 0;
            $failed = 0;
            $skipped = 0;
            $chunk = [];

            foreach ($reader->rows() as $rowNumber => $rowData) {
                $this->checkCancellation($import);

                $chunk[] = [$rowNumber, $rowData];

                if (count($chunk) >= $this->chunkSize) {
                    $result = $this->processChunk($import, $chunk);
                    $processed += count($chunk);
                    $successful += $result['successful'];
                    $failed += $result['failed'];
                    $skipped += $result['skipped'];
                    $chunk = [];

                    $this->updateProgress($import, $processed, $successful, $failed, $skipped);
                }
            }

            if ($chunk !== []) {
                $result = $this->processChunk($import, $chunk);
                $processed += count($chunk);
                $successful += $result['successful'];
                $failed += $result['failed'];
                $skipped += $result['skipped'];
            }

            $reader->close();

            $import->update([
                'processed_rows' => $processed,
                'successful_rows' => $successful,
                'failed_rows' => $failed,
                'skipped_rows' => $skipped,
            ]);

            $import->markAsCompleted();
            Event::dispatch(new ImportCompleted($import));
        } catch (ImportCancelledException) {
            $import->markAsCancelled();
            Event::dispatch(new ImportCancelled($import));
        } catch (\Throwable $e) {
            $import->markAsFailed($e->getMessage());
            Event::dispatch(new ImportFailed($import, $e));

            throw $e;
        }
    }

    /**
     * @param  list<array{0: int, 1: array<string, mixed>}>  $chunk
     * @return array{successful: int, failed: int, skipped: int}
     */
    private function processChunk(ImportModel $import, array $chunk): array
    {
        $successful = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($chunk as [$rowNumber, $rawData]) {
            $rowResult = $this->processRow($import, $rowNumber, $rawData);

            match ($rowResult) {
                'success' => $successful++,
                'failed' => $failed++,
                'skipped' => $skipped++,
                default => null,
            };
        }

        return compact('successful', 'failed', 'skipped');
    }

    /**
     * @param  array<string, mixed>  $rawData
     */
    private function processRow(ImportModel $import, int $rowNumber, array $rawData): string
    {
        $rowRecord = ImportRow::create([
            'import_id' => $import->id,
            'row_number' => $rowNumber,
            'data' => $rawData,
            'status' => ImportRowStatus::Processing,
        ]);

        try {
            // Map columns
            $mappedData = $this->mapper->map($rawData);

            // Validate row
            if ($this->rowValidator->getRules() !== []) {
                $errors = $this->rowValidator->validate($mappedData);

                if ($errors !== []) {
                    $this->recordErrors($import, $rowRecord, $rowNumber, $errors);
                    $rowRecord->markAsFailed('Validation failed');

                    return 'failed';
                }
            }

            // Check duplicates
            if ($this->duplicateDetector->isDuplicate($mappedData)) {
                if ($this->duplicateDetector->shouldSkip()) {
                    $rowRecord->markAsSkipped('Duplicate detected');
                    $this->recordError($import, $rowRecord, $rowNumber, null, 'Duplicate skipped');

                    return 'skipped';
                }

                if ($this->duplicateDetector->shouldReject()) {
                    $rowRecord->markAsFailed('Duplicate detected');
                    $this->recordError($import, $rowRecord, $rowNumber, null, 'Duplicate rejected');

                    return 'failed';
                }
            }

            // Transform data
            $transformedData = $this->transformerPipeline->transform($mappedData);

            // Upsert or create
            if ($this->duplicateDetector->shouldUpsert() && $this->duplicateDetector->isDuplicate($mappedData)) {
                $this->upsertRecord($transformedData);
            } else {
                $this->modelClass::create($transformedData);
            }

            $rowRecord->update([
                'mapped_data' => $transformedData,
                'status' => ImportRowStatus::Success,
                'processed_at' => now(),
            ]);

            return 'success';
        } catch (\Throwable $e) {
            $rowRecord->markAsFailed($e->getMessage());
            $this->recordError($import, $rowRecord, $rowNumber, null, $e->getMessage());

            return 'failed';
        }
    }

    /**
     * @param  array<string, list<string>>  $errors
     */
    private function recordErrors(ImportModel $import, ImportRow $rowRecord, int $rowNumber, array $errors): void
    {
        foreach ($errors as $column => $columnErrors) {
            foreach ($columnErrors as $error) {
                ImportError::create([
                    'import_id' => $import->id,
                    'import_row_id' => $rowRecord->id,
                    'row_number' => $rowNumber,
                    'column' => $column,
                    'error' => $error,
                ]);
            }
        }
    }

    private function recordError(ImportModel $import, ?ImportRow $rowRecord, int $rowNumber, ?string $column, string $error): void
    {
        ImportError::create([
            'import_id' => $import->id,
            'import_row_id' => $rowRecord?->id,
            'row_number' => $rowNumber,
            'column' => $column,
            'error' => $error,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function upsertRecord(array $data): void
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = $this->modelClass;
        $key = $this->duplicateDetector->getUniqueKey() ?? 'id';
        $keyValue = $data[$key] ?? null;

        if ($keyValue !== null) {
            $modelClass::updateOrCreate([$key => $keyValue], $data);
        } else {
            $modelClass::create($data);
        }
    }

    private function updateProgress(ImportModel $import, int $processed, int $successful, int $failed, int $skipped): void
    {
        $import->update([
            'processed_rows' => $processed,
            'successful_rows' => $successful,
            'failed_rows' => $failed,
            'skipped_rows' => $skipped,
        ]);

        Event::dispatch(new ImportProgress($import));
    }

    private function checkCancellation(ImportModel $import): void
    {
        $import->refresh();

        if ($import->status === ImportStatus::Cancelled) {
            throw new ImportCancelledException($import->id);
        }
    }
}
