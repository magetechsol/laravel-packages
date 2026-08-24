<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Readers;

use Generator;

interface ReaderInterface
{
    /**
     * Open a file for reading.
     */
    public function open(string $path): void;

    /**
     * Read rows as a generator for memory-efficient streaming.
     *
     * @return Generator<array-key, array<string, mixed>>
     */
    public function rows(): Generator;

    /**
     * Get the total number of rows (excluding header if present).
     */
    public function totalRows(): int;

    /**
     * Get the header row column names.
     *
     * @return list<string>
     */
    public function headers(): array;

    /**
     * Close the reader and free resources.
     */
    public function close(): void;
}
