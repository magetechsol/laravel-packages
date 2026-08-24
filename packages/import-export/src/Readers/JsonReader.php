<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Readers;

use Generator;
use MageTech\ImportExport\Exceptions\ImportException;

final class JsonReader implements ReaderInterface
{
    private array $headers = [];

    private int $totalRows = 0;

    private string $path = '';

    private ?array $data = null;

    private string $rowsKey;

    public function __construct(
        ?string $rowsKey = null,
    ) {
        $this->rowsKey = $rowsKey ?? 'data';
    }

    public function open(string $path): void
    {
        if (! file_exists($path)) {
            throw ImportException::fileNotFound($path);
        }

        $this->path = $path;
        $content = file_get_contents($path);

        if ($content === false) {
            throw ImportException::cannotOpenFile($path);
        }

        $decoded = json_decode($content, true);

        if (! is_array($decoded)) {
            throw ImportException::invalidJson($path);
        }

        if (isset($decoded[$this->rowsKey]) && is_array($decoded[$this->rowsKey])) {
            $this->data = $decoded[$this->rowsKey];
        } elseif (array_is_list($decoded)) {
            $this->data = $decoded;
        } else {
            $this->data = [$decoded];
        }

        $this->totalRows = count($this->data);

        if ($this->totalRows > 0) {
            $this->headers = array_keys($this->data[0]);
        }
    }

    public function rows(): Generator
    {
        if ($this->data === null) {
            return;
        }

        $rowNumber = 0;

        foreach ($this->data as $row) {
            $rowNumber++;

            if (! array_is_list($row)) {
                yield $rowNumber => $row;
            } else {
                if ($this->headers !== []) {
                    yield $rowNumber => array_combine($this->headers, $row);
                } else {
                    yield $rowNumber => $row;
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
        $this->data = null;
        $this->headers = [];
        $this->totalRows = 0;
    }
}
