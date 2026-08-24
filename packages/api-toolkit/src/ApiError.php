<?php

declare(strict_types=1);

namespace MageTech\ApiToolkit;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use JsonSerializable;
use MageTech\ApiToolkit\DTOs\ErrorData;
use MageTech\ApiToolkit\DTOs\ValidationErrorData;

class ApiError implements Jsonable, JsonSerializable, Responsable
{
    public function __construct(
        protected string $message,
        protected int $statusCode = 400,
        protected ?string $errorCode = null,
        protected ?array $details = null,
    ) {}

    /**
     * Create a new ApiError instance.
     *
     * @param  string  $message
     * @param  int  $statusCode
     * @param  string|null  $errorCode
     * @param  array|null  $details
     */
    public static function new(string $message, int $statusCode = 400, ?string $errorCode = null, ?array $details = null): static
    {
        return new static($message, $statusCode, $errorCode, $details);
    }

    /**
     * Create a validation error.
     *
     * @param  array<string, array<string>>  $errors
     * @param  string  $message
     */
    public static function validation(array $errors, string $message = 'Validation failed'): static
    {
        return new static(
            message: $message,
            statusCode: 422,
            errorCode: 'VALIDATION_ERROR',
            details: $errors,
        );
    }

    /**
     * Create an unauthorized error.
     *
     * @param  string  $message
     */
    public static function unauthorized(string $message = 'Unauthorized'): static
    {
        return new static($message, 401, 'UNAUTHORIZED');
    }

    /**
     * Create a forbidden error.
     *
     * @param  string  $message
     */
    public static function forbidden(string $message = 'Forbidden'): static
    {
        return new static($message, 403, 'FORBIDDEN');
    }

    /**
     * Create a not found error.
     *
     * @param  string  $message
     */
    public static function notFound(string $message = 'Not Found'): static
    {
        return new static($message, 404, 'NOT_FOUND');
    }

    /**
     * Create a conflict error.
     *
     * @param  string  $message
     */
    public static function conflict(string $message = 'Conflict'): static
    {
        return new static($message, 409, 'CONFLICT');
    }

    /**
     * Create a rate limit error.
     *
     * @param  string  $message
     */
    public static function rateLimited(string $message = 'Too Many Requests'): static
    {
        return new static($message, 429, 'RATE_LIMITED');
    }

    /**
     * Create a server error.
     *
     * @param  string  $message
     */
    public static function serverError(string $message = 'Internal Server Error'): static
    {
        return new static($message, 500, 'SERVER_ERROR');
    }

    /**
     * Get the error message.
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Get the status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the error code.
     */
    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    /**
     * Get the error details.
     *
     * @return array|null
     */
    public function getDetails(): ?array
    {
        return $this->details;
    }

    /**
     * Convert to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $errorData = new ErrorData(
            code: $this->errorCode ?? $this->guessErrorCode($this->statusCode),
            type: $this->guessErrorType($this->statusCode),
            message: $this->message,
            details: $this->details,
        );

        return $errorData->toArray();
    }

    /**
     * Convert to JSON.
     *
     * @param  int  $options
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Convert to JSON serializable array.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Convert to JSON response.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toResponse($request): JsonResponse
    {
        return response()->json($this->toArray(), $this->statusCode);
    }

    /**
     * Guess error code from HTTP status.
     *
     * @param  int  $code
     */
    protected function guessErrorCode(int $code): string
    {
        return match (true) {
            $code === 400 => 'BAD_REQUEST',
            $code === 401 => 'UNAUTHORIZED',
            $code === 403 => 'FORBIDDEN',
            $code === 404 => 'NOT_FOUND',
            $code === 405 => 'METHOD_NOT_ALLOWED',
            $code === 409 => 'CONFLICT',
            $code === 422 => 'UNPROCESSABLE_ENTITY',
            $code === 429 => 'TOO_MANY_REQUESTS',
            $code === 500 => 'INTERNAL_ERROR',
            $code === 503 => 'SERVICE_UNAVAILABLE',
            default => 'ERROR',
        };
    }

    /**
     * Guess error type from HTTP status.
     *
     * @param  int  $code
     */
    protected function guessErrorType(int $code): string
    {
        return match (true) {
            $code >= 400 && $code < 422 => 'client',
            $code === 422 => 'validation',
            $code === 429 => 'rate_limit',
            $code >= 500 => 'server',
            default => 'unknown',
        };
    }
}
