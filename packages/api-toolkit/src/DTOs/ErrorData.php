<?php

declare(strict_types=1);

namespace MageTech\ApiToolkit\DTOs;

readonly class ErrorData
{
    public function __construct(
        public string $code,
        public string $type,
        public string $message,
        public ?array $details = null,
    ) {}

    /**
     * Create error data for validation errors.
     *
     * @param  array<string, array<string>>  $errors
     * @param  string  $message
     */
    public static function validation(array $errors, string $message = 'Validation failed'): static
    {
        return new static(
            code: 'VALIDATION_ERROR',
            type: 'validation',
            message: $message,
            details: $errors,
        );
    }

    /**
     * Create error data for authentication errors.
     *
     * @param  string  $message
     */
    public static function unauthorized(string $message = 'Unauthorized'): static
    {
        return new static(
            code: 'UNAUTHORIZED',
            type: 'authentication',
            message: $message,
        );
    }

    /**
     * Create error data for authorization errors.
     *
     * @param  string  $message
     */
    public static function forbidden(string $message = 'Forbidden'): static
    {
        return new static(
            code: 'FORBIDDEN',
            type: 'authorization',
            message: $message,
        );
    }

    /**
     * Create error data for not found errors.
     *
     * @param  string  $message
     */
    public static function notFound(string $message = 'Not Found'): static
    {
        return new static(
            code: 'NOT_FOUND',
            type: 'not_found',
            message: $message,
        );
    }

    /**
     * Create error data for conflict errors.
     *
     * @param  string  $message
     */
    public static function conflict(string $message = 'Conflict'): static
    {
        return new static(
            code: 'CONFLICT',
            type: 'conflict',
            message: $message,
        );
    }

    /**
     * Create error data for rate limit errors.
     *
     * @param  string  $message
     */
    public static function rateLimited(string $message = 'Too Many Requests'): static
    {
        return new static(
            code: 'RATE_LIMITED',
            type: 'rate_limit',
            message: $message,
        );
    }

    /**
     * Create error data for server errors.
     *
     * @param  string  $message
     */
    public static function serverError(string $message = 'Internal Server Error'): static
    {
        return new static(
            code: 'SERVER_ERROR',
            type: 'server',
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
        $error = [
            'code' => $this->code,
            'type' => $this->type,
            'message' => $this->message,
        ];

        if ($this->details !== null) {
            $error['details'] = $this->details;
        }

        return $error;
    }
}
