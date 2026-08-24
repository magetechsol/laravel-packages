<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Readers;

use Generator;
use MageTech\ImportExport\Exceptions\ImportException;

final class CsvReader implements ReaderInterface
{
    private $handle = null;

    private array $headers = [];

    private int $totalRows = 0;

    private string $delimiter;

    private string $enclosure;

    private string $escape;

    private bool $hasHeader;

    public function __construct(
        ?string $delimiter = null,
        ?string $enclosure = null,
        ?string $escape = null,
        ?bool $hasHeader = null,
    ) {
        $this->delimiter = $delimiter ?? config('mts-import-export.csv.delimiter', ',');
        $this->enclosure = $enclosure ?? config('mts-import-export.csv.enclosure', '"');
        $this->escape = $escape ?? config('mts-import-export.csv.escape', '\\');
        $this->hasHeader = $hasHeader ?? config('mts-import-export.csv.has_header', true);
    }

    public function open(string $path): void
    {
        if (! file_exists($path)) {
            throw ImportException::fileNotFound($path);
        }

        $this->handle = fopen($path, 'rb');

        if ($this->handle === false) {
            throw ImportException::cannotOpenFile($path);
        }

        if ($this->hasHeader) {
            $this->headers = $this->readRow() ?? [];
        }

        $this->countTotalRows($path);
    }

    public function rows(): Generator
    {
        if ($this->handle === null) {
            return;
        }

        $rowNumber = $this->hasHeader ? 1 : 0;

        while (($row = $this->readRow()) !== null) {
            $rowNumber++;

            if ($this->headers !== []) {
                yield $rowNumber => array_combine($this->headers, $row);
            } else {
                yield $rowNumber => $row;
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
        if ($this->handle !== null) {
            fclose($this->handle);
            $this->handle = null;
        }
    }

    private function readRow(): ?array
    {
        if ($this->handle === null) {
            return null;
        }

        $row = fgetcsv($this->handle, 0, $this->delimiter, $this->enclosure, $this->escape);

        return $row === false ? null : $row;
    }

    private function countTotalRows(string $path): void
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            $this->totalRows = 0;

            return;
        }

        $count = 0;

        while (fgetcsv($handle, 0, $this->delimiter, $this->enclosure, $this->escape) !== false) {
            $count++;
        }

        fclose($handle);

        $this->totalRows = $this->hasHeader ? max(0, $count - 1) : $count;
    }
}
