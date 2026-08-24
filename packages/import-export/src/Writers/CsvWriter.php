<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Writers;

use MageTech\ImportExport\Exceptions\ExportException;

final class CsvWriter implements WriterInterface
{
    private $handle = null;

    private int $rowsWritten = 0;

    private string $delimiter;

    private string $enclosure;

    private string $escape;

    private bool $headersWritten = false;

    private ?array $headers = null;

    public function __construct(
        ?string $delimiter = null,
        ?string $enclosure = null,
        ?string $escape = null,
    ) {
        $this->delimiter = $delimiter ?? config('mts-import-export.csv.delimiter', ',');
        $this->enclosure = $enclosure ?? config('mts-import-export.csv.enclosure', '"');
        $this->escape = $escape ?? config('mts-import-export.csv.escape', '\\');
    }

    public function open(string $path): void
    {
        $this->handle = @fopen($path, 'wb');

        if ($this->handle === false) {
            throw ExportException::cannotOpenFile($path);
        }
    }

    public function write(array $rows): void
    {
        if ($this->handle === null) {
            return;
        }

        if ($this->headers !== null && ! $this->headersWritten) {
            fputcsv($this->handle, $this->headers, $this->delimiter, $this->enclosure, $this->escape);
            $this->headersWritten = true;
        }

        foreach ($rows as $row) {
            fputcsv($this->handle, array_values($row), $this->delimiter, $this->enclosure, $this->escape);
            $this->rowsWritten++;
        }
    }

    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    public function close(): void
    {
        if ($this->handle !== null) {
            fclose($this->handle);
            $this->handle = null;
        }
    }

    public function getRowsWritten(): int
    {
        return $this->rowsWritten;
    }
}
