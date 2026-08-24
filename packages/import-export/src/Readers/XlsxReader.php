<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Readers;

use Generator;
use MageTech\ImportExport\Exceptions\ImportException;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\Common\Creator\ReaderEntityFactory;
use OpenSpout\Reader\XLSX\Reader;

final class XlsxReader implements ReaderInterface
{
    private ?Reader $reader = null;

    private array $headers = [];

    private int $totalRows = 0;

    private bool $hasHeader;

    public function __construct(
        ?bool $hasHeader = null,
    ) {
        $this->hasHeader = $hasHeader ?? config('mts-import-export.csv.has_header', true);
    }

    public function open(string $path): void
    {
        if (! file_exists($path)) {
            throw ImportException::fileNotFound($path);
        }

        $this->reader = ReaderEntityFactory::createXLSXReader();
        $this->reader->open($path);

        $sheetIterator = $this->reader->getSheetIterator();
        $sheetIterator->rewind();

        if ($sheetIterator->valid()) {
            $sheet = $sheetIterator->current();
            $rowIterator = $sheet->getRowIterator();

            $rowIterator->rewind();

            if ($this->hasHeader && $rowIterator->valid()) {
                $headerRow = $rowIterator->current();
                $this->headers = $this->extractRowValues($headerRow);
            }
        }
    }

    public function rows(): Generator
    {
        if ($this->reader === null) {
            return;
        }

        $rowNumber = $this->hasHeader ? 1 : 0;

        foreach ($this->reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rowNumber++;

                if ($this->hasHeader && $rowNumber === 2) {
                    continue; // Skip header row
                }

                $values = $this->extractRowValues($row);

                if ($this->headers !== []) {
                    yield $rowNumber => array_combine($this->headers, $values);
                } else {
                    yield $rowNumber => $values;
                }
            }
        }
    }

    public function totalRows(): int
    {
        return $this->totalRows;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function close(): void
    {
        if ($this->reader !== null) {
            $this->reader->close();
            $this->reader = null;
        }
    }

    private function extractRowValues(Row $row): array
    {
        $values = [];

        foreach ($row->getCells() as $cell) {
            $values[] = $cell->getValue() ?? '';
        }

        return $values;
    }
}
