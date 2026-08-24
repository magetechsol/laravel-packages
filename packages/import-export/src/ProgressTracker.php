<?php

declare(strict_types=1);

namespace MageTech\ImportExport;

use MageTech\ImportExport\DTOs\ImportProgress as ImportProgressDTO;
use MageTech\ImportExport\Models\Import;

final class ProgressTracker
{
    public function getProgress(Import $import): ImportProgressDTO
    {
        return new ImportProgressDTO(
            totalRows: $import->total_rows,
            processedRows: $import->processed_rows,
            successfulRows: $import->successful_rows,
            failedRows: $import->failed_rows,
            skippedRows: $import->skipped_rows,
            status: $import->status,
        );
    }

    public function update(Import $import, array $counts): void
    {
        $import->update($counts);
    }

    public function incrementProcessed(Import $import, int $count = 1): void
    {
        $import->increment('processed_rows', $count);
    }

    public function incrementSuccessful(Import $import, int $count = 1): void
    {
        $import->increment('successful_rows', $count);
    }

    public function incrementFailed(Import $import, int $count = 1): void
    {
        $import->increment('failed_rows', $count);
    }

    public function incrementSkipped(Import $import, int $count = 1): void
    {
        $import->increment('skipped_rows', $count);
    }
}
