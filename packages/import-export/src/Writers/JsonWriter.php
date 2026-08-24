<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Writers;

use MageTech\ImportExport\Exceptions\ExportException;

final class JsonWriter implements WriterInterface
{
    private $handle = null;

    private int $rowsWritten = 0;

    private bool $firstRow = true;

    private ?array $headers = null;

    public function open(string $path): void
    {
        $this->handle = @fopen($path, 'wb');

        if ($this->handle === false) {
            throw ExportException::cannotOpenFile($path);
        }

        fwrite($this->handle, '[');
    }

    public function write(array $rows): void
    {
        if ($this->handle === null) {
            return;
        }

        foreach ($rows as $row) {
            if (! $this->firstRow) {
                fwrite($this->handle, ',');
            }

            $json = json_encode(array_values($row), JSON_THROW_ON_ERROR);
            fwrite($this->handle, $json);

            $this->firstRow = false;
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
            fwrite($this->handle, ']');
            fclose($this->handle);
            $this->handle = null;
        }
    }

    public function getRowsWritten(): int
    {
        return $this->rowsWritten;
    }
}
