<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Writers;

use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row as OpenSpoutRow;
use OpenSpout\Writer\Common\Creator\WriterEntityFactory;
use OpenSpout\Writer\XLSX\Writer;

final class XlsxWriter implements WriterInterface
{
    private ?Writer $writer = null;

    private int $rowsWritten = 0;

    private ?array $headers = null;

    public function open(string $path): void
    {
        $this->writer = WriterEntityFactory::createXLSXWriter();
        $this->writer->openToFile($path);
    }

    public function write(array $rows): void
    {
        if ($this->writer === null) {
            return;
        }

        if ($this->headers !== null && $this->rowsWritten === 0) {
            $headerCells = array_map(
                fn (string $h) => Cell::fromValue($h),
                $this->headers,
            );
            $headerRow = new OpenSpoutRow($headerCells);
            $this->writer->addRow($headerRow);
        }

        foreach ($rows as $row) {
            $cells = array_map(
                fn ($value) => Cell::fromValue($value),
                array_values($row),
            );
            $spoutRow = new OpenSpoutRow($cells);
            $this->writer->addRow($spoutRow);
            $this->rowsWritten++;
        }
    }

    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    public function close(): void
    {
        if ($this->writer !== null) {
            $this->writer->close();
            $this->writer = null;
        }
    }

    public function getRowsWritten(): int
    {
        return $this->rowsWritten;
    }
}
