<?php

declare(strict_types=1);

namespace MageTech\ApiToolkit\ExceptionHandling;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;

class ExceptionMapper
{
    /**
     * Map exception class to HTTP status code.
     *
     * @var array<class-string, int>
     */
    protected array $exceptionMap = [];

    /**
     * Create a new ExceptionMapper instance.
     */
    public function __construct()
    {
        $this->exceptionMap = config('mts-api.exception_map', []);
    }

    /**
     * Get the status code for an exception.
     *
     * @param  \Throwable  $exception
     */
    public function getStatusCode(\Throwable $exception): int
    {
        foreach ($this->exceptionMap as $exceptionClass => $statusCode) {
            if ($exception instanceof $exceptionClass) {
                return $statusCode;
            }
        }

        if (method_exists($exception, 'getStatusCode')) {
            return $exception->getStatusCode();
        }

        return 500;
    }

    /**
     * Get the error code for an exception.
     *
     * @param  \Throwable  $exception
     */
    public function getErrorCode(\Throwable $exception): string
    {
        return match (true) {
            $exception instanceof ValidationException => 'VALIDATION_ERROR',
            $exception instanceof AuthenticationException => 'UNAUTHORIZED',
            $exception instanceof AuthorizationException => 'FORBIDDEN',
            $exception instanceof ModelNotFoundException => 'NOT_FOUND',
            $exception instanceof ThrottleRequestsException => 'RATE_LIMITED',
            default => $this->guessErrorCode($this->getStatusCode($exception)),
        };
    }

    /**
     * Get the error type for an exception.
     *
     * @param  \Throwable  $exception
     */
    public function getErrorType(\Throwable $exception): string
    {
        return match (true) {
            $exception instanceof ValidationException => 'validation',
            $exception instanceof AuthenticationException => 'authentication',
            $exception instanceof AuthorizationException => 'authorization',
            $exception instanceof ModelNotFoundException => 'not_found',
            $exception instanceof ThrottleRequestsException => 'rate_limit',
            default => $this->guessErrorType($this->getStatusCode($exception)),
        };
    }

    /**
     * Check if an exception should be logged.
     *
     * @param  \Throwable  $exception
     */
    public function shouldLog(\Throwable $exception): bool
    {
        $statusCode = $this->getStatusCode($exception);

        return $statusCode >= 500;
    }

    /**
     * Check if stack traces should be hidden.
     */
    public function shouldHideStackTraces(): bool
    {
        return config('mts-api.exception_handling.hide_stack_traces', true);
    }

    /**
     * Check if exception handling is enabled.
     */
    public function isEnabled(): bool
    {
        return config('mts-api.exception_handling.enabled', true);
    }

    /**
     * Register a custom exception mapping.
     *
     * @param  class-string  $exceptionClass
     * @param  int  $statusCode
     */
    public function registerMapping(string $exceptionClass, int $statusCode): static
    {
        $this->exceptionMap[$exceptionClass] = $statusCode;

        return $this;
    }

    /**
     * Get all exception mappings.
     *
     * @return array<class-string, int>
     */
    public function getMappings(): array
    {
        return $this->exceptionMap;
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
