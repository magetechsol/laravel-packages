<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Writers;

use MageTech\ImportExport\Exceptions\ExportException;
use XMLWriter;

final class XmlWriter implements WriterInterface
{
    private ?XMLWriter $writer = null;

    private int $rowsWritten = 0;

    private string $rootElement;

    private string $rowElement;

    private bool $rootOpened = false;

    private ?array $headers = null;

    public function __construct(
        ?string $rootElement = null,
        ?string $rowElement = null,
    ) {
        $this->rootElement = $rootElement ?? config('mts-import-export.xml.root_element', 'records');
        $this->rowElement = $rowElement ?? config('mts-import-export.xml.row_element', 'record');
    }

    public function open(string $path): void
    {
        $this->writer = new XMLWriter;

        $result = $this->writer->openUri('file://'.$path);

        if ($result === false) {
            throw ExportException::cannotOpenFile($path);
        }

        $this->writer->startDocument('1.0', 'UTF-8');
        $this->writer->startElement($this->rootElement);
        $this->rootOpened = true;
    }

    public function write(array $rows): void
    {
        if ($this->writer === null) {
            return;
        }

        foreach ($rows as $row) {
            $this->writer->startElement($this->rowElement);

            foreach ($row as $key => $value) {
                $this->writer->writeElement((string) $key, (string) ($value ?? ''));
            }

            $this->writer->endElement();
            $this->rowsWritten++;
        }
    }

    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    public function close(): void
    {
        if ($this->writer !== null && $this->rootOpened) {
            $this->writer->endElement(); // root
            $this->writer->endDocument();
            $this->writer->flush();
            $this->writer = null;
            $this->rootOpened = false;
        }
    }

    public function getRowsWritten(): int
    {
        return $this->rowsWritten;
    }
}
