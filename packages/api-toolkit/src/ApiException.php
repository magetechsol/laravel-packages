<?php

declare(strict_types=1);

namespace MageTech\ApiToolkit;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use RuntimeException;

class ApiException extends RuntimeException implements HttpExceptionInterface
{
    public function __construct(
        protected string $message,
        protected int $statusCode = 400,
        protected ?string $errorCode = null,
        protected ?array $data = null,
        protected array $headers = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    /**
     * Create a new ApiException instance.
     *
     * @param  string  $message
     * @param  int  $statusCode
     * @param  string|null  $errorCode
     * @param  array|null  $data
     * @param  array  $headers
     */
    public static function new(
        string $message,
        int $statusCode = 400,
        ?string $errorCode = null,
        ?array $data = null,
        array $headers = [],
    ): static {
        return new static($message, $statusCode, $errorCode, $data, $headers);
    }

    /**
     * Create a validation exception.
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
            data: ['errors' => $errors],
        );
    }

    /**
     * Create an unauthorized exception.
     *
     * @param  string  $message
     */
    public static function unauthorized(string $message = 'Unauthenticated.'): static
    {
        return new static($message, 401, 'UNAUTHORIZED');
    }

    /**
     * Create a forbidden exception.
     *
     * @param  string  $message
     */
    public static function forbidden(string $message = 'Forbidden.'): static
    {
        return new static($message, 403, 'FORBIDDEN');
    }

    /**
     * Create a not found exception.
     *
     * @param  string  $message
     */
    public static function notFound(string $message = 'Not Found.'): static
    {
        return new static($message, 404, 'NOT_FOUND');
    }

    /**
     * Create a conflict exception.
     *
     * @param  string  $message
     */
    public static function conflict(string $message = 'Conflict.'): static
    {
        return new static($message, 409, 'CONFLICT');
    }

    /**
     * Create a rate limit exception.
     *
     * @param  string  $message
     */
    public static function throttle(string $message = 'Too Many Requests.'): static
    {
        return new static($message, 429, 'RATE_LIMITED');
    }

    /**
     * Create a server error exception.
     *
     * @param  string  $message
     */
    public static function serverError(string $message = 'Internal Server Error.'): static
    {
        return new static($message, 500, 'SERVER_ERROR');
    }

    /**
     * Create from a generic exception.
     *
     * @param  \Throwable  $e
     * @param  bool  $hideStackTraces
     */
    public static function fromThrowable(\Throwable $e, bool $hideStackTraces = true): static
    {
        $message = $hideStackTraces
            ? 'An error occurred while processing your request.'
            : $e->getMessage();

        $statusCode = $e instanceof HttpExceptionInterface
            ? $e->getStatusCode()
            : 500;

        return new static(
            message: $message,
            statusCode: $statusCode,
            errorCode: 'SERVER_ERROR',
            previous: $hideStackTraces ? null : $e,
        );
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
     * Get the data.
     *
     * @return array|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * Get the headers.
     *
     * @return array<string, mixed>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Render the exception as an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function render($request): JsonResponse
    {
        $errorData = [
            'code' => $this->errorCode ?? $this->guessErrorCode($this->statusCode),
            'type' => $this->guessErrorType($this->statusCode),
            'message' => $this->message,
        ];

        if ($this->data !== null) {
            $errorData['details'] = $this->data;
        }

        $response = [
            'success' => false,
            'message' => $this->message,
            'error' => $errorData,
        ];

        return response()->json($response, $this->statusCode, $this->headers);
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
