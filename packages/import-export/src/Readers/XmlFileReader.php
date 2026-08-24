<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Readers;

use Generator;
use MageTech\ImportExport\Exceptions\ImportException;
use XMLReader;

final class XmlFileReader implements ReaderInterface
{
    private ?XMLReader $reader = null;

    private array $headers = [];

    private int $totalRows = 0;

    private string $rowElement;

    private bool $hasHeader;

    private bool $headersExtracted = false;

    public function __construct(
        ?string $rowElement = null,
        ?bool $hasHeader = null,
    ) {
        $this->rowElement = $rowElement ?? config('mts-import-export.xml.row_element', 'record');
        $this->hasHeader = $hasHeader ?? config('mts-import-export.csv.has_header', true);
    }

    public function open(string $path): void
    {
        if (! file_exists($path)) {
            throw ImportException::fileNotFound($path);
        }

        $this->reader = new XMLReader;

        if (! $this->reader->open($path)) {
            throw ImportException::cannotOpenFile($path);
        }
    }

    public function rows(): Generator
    {
        if ($this->reader === null) {
            return;
        }

        $rowNumber = 0;

        while ($this->reader->read()) {
            if ($this->reader->nodeType === XMLReader::ELEMENT && $this->reader->localName === $this->rowElement) {
                $rowNumber++;

                $rowData = $this->parseRow();

                if ($this->hasHeader && ! $this->headersExtracted) {
                    $this->headers = array_keys($rowData);
                    $this->headersExtracted = true;
                }

                if ($this->headers !== []) {
                    yield $rowNumber => $rowData;
                } else {
                    yield $rowNumber => $rowData;
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

    private function parseRow(): array
    {
        if ($this->reader === null) {
            return [];
        }

        $node = $this->reader->expand();

        if ($node === false) {
            return [];
        }

        $data = [];

        foreach ($node->childNodes as $child) {
            if ($child->nodeType === \XML_ELEMENT_NODE) {
                $data[$child->localName] = $child->textContent;
            }
        }

        return $data;
    }
}
