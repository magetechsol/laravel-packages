<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Writers;

interface WriterInterface
{
    /**
     * Open a file for writing.
     */
    public function open(string $path): void;

    /**
     * Write a chunk of rows.
     *
     * @param  list<array<string, mixed>>  $rows
     */
    public function write(array $rows): void;

    /**
     * Set the header row.
     *
     * @param  list<string>  $headers
     */
    public function setHeaders(array $headers): void;

    /**
     * Close the writer and finalize the file.
     */
    public function close(): void;

    /**
     * Get the number of rows written.
     */
    public function getRowsWritten(): int;
}
