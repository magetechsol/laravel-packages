<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Exceptions;

use RuntimeException;

class FileValidationException extends RuntimeException
{
    private array $errors;

    /**
     * @param  list<string>  $errors
     */
    public function __construct(array $errors, string $message = 'File validation failed')
    {
        $this->errors = $errors;

        parent::__construct($message.': '.implode(' ', $errors));
    }

    public static function withErrors(array $errors): static
    {
        return new static($errors);
    }

    public static function invalidMime(string $expected, string $actual): static
    {
        return new static(["Expected MIME type '{$expected}', got '{$actual}'."]);
    }

    public static function sizeExceeded(int $max, int $actual): static
    {
        $maxMb = round($max / (1024 * 1024), 2);
        $actualMb = round($actual / (1024 * 1024), 2);

        return new static(["File size {$actualMb}MB exceeds maximum of {$maxMb}MB."]);
    }

    /**
     * @return list<string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
