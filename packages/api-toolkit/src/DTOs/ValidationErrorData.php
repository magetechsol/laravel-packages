<?php

declare(strict_types=1);

namespace MageTech\ApiToolkit\DTOs;

readonly class ValidationErrorData
{
    public function __construct(
        public array $errors,
        public string $message = 'Validation failed',
    ) {}

    /**
     * Create from Laravel ValidationException.
     *
     * @param  \Illuminate\Validation\ValidationException  $exception
     */
    public static function fromException(\Illuminate\Validation\ValidationException $exception): static
    {
        return new static(
            errors: $exception->errors(),
            message: $exception->getMessage(),
        );
    }

    /**
     * Create from array of errors.
     *
     * @param  array<string, array<string>>  $errors
     * @param  string  $message
     */
    public static function fromArray(array $errors, string $message = 'Validation failed'): static
    {
        return new static(
            errors: $errors,
            message: $message,
        );
    }

    /**
     * Convert to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'code' => 'VALIDATION_ERROR',
            'type' => 'validation',
            'message' => $this->message,
            'details' => $this->errors,
        ];
    }
}
