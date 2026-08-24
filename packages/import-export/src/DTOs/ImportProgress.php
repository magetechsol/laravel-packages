<?php

declare(strict_types=1);

namespace MageTech\ImportExport\DTOs;

use MageTech\ImportExport\Enums\ImportStatus;

final readonly class ImportProgress
{
    public function __construct(
        public int $totalRows,
        public int $processedRows,
        public int $successfulRows,
        public int $failedRows,
        public int $skippedRows,
        public ImportStatus $status,
    ) {
    }

    public function percentage(): float
    {
        if ($this->totalRows === 0) {
            return 0.0;
        }

        return round(($this->processedRows / $this->totalRows) * 100, 2);
    }

    public function toArray(): array
    {
        return [
            'total_rows' => $this->totalRows,
            'processed_rows' => $this->processedRows,
            'successful_rows' => $this->successfulRows,
            'failed_rows' => $this->failedRows,
            'skipped_rows' => $this->skippedRows,
            'percentage' => $this->percentage(),
            'status' => $this->status->value,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }
}
