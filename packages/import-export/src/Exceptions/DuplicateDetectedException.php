<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Exceptions;

use RuntimeException;

class DuplicateDetectedException extends RuntimeException
{
    private array $duplicates;

    /**
     * @param  array<int, array<string, mixed>>  $duplicates
     */
    public function __construct(array $duplicates = [])
    {
        $this->duplicates = $duplicates;

        $count = count($duplicates);
        parent::__construct("{$count} duplicate(s) detected.");
    }

    public static function withDuplicates(array $duplicates): static
    {
        return new static($duplicates);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getDuplicates(): array
    {
        return $this->duplicates;
    }
}
