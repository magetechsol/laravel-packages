<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Exceptions;

use RuntimeException;

class RowValidationException extends RuntimeException
{
    private int $rowNumber;

    private array $errors;

    /**
     * @param  array<string, list<string>>  $errors
     */
    public function __construct(int $rowNumber, array $errors)
    {
        $this->rowNumber = $rowNumber;
        $this->errors = $errors;

        $count = count($errors);
        parent::__construct("Row #{$rowNumber} has {$count} validation error(s).");
    }

    public static function forRow(int $rowNumber, array $errors): static
    {
        return new static($rowNumber, $errors);
    }

    public function getRowNumber(): int
    {
        return $this->rowNumber;
    }

    /**
     * @return array<string, list<string>>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
