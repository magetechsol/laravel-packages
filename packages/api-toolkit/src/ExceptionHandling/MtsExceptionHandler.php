<?php

declare(strict_types=1);

namespace MageTech\ApiToolkit\ExceptionHandling;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use MageTech\ApiToolkit\ApiException;
use MageTech\ApiToolkit\ApiResponse;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class MtsExceptionHandler extends ExceptionHandler
{
    protected ExceptionMapper $exceptionMapper;

    /**
     * Create a new exception handler instance.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @param  \Psr\Log\LoggerInterface  $log
     */
    public function __construct($app, \Psr\Log\LoggerInterface $log)
    {
        parent::__construct($app, $log);
        $this->exceptionMapper = new ExceptionMapper();
    }

    /**
     * Register the exception handling callbacks.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     */
    public function render($request, Throwable $e): JsonResponse
    {
        if ($this->exceptionMapper->isEnabled() === false) {
            return parent::render($request, $e);
        }

        if ($e instanceof ApiException) {
            return $e->render($request);
        }

        if ($e instanceof ValidationException) {
            return ApiResponse::validation($e);
        }

        if ($e instanceof AuthenticationException) {
            return ApiResponse::unauthorized($e->getMessage());
        }

        if ($e instanceof AuthorizationException) {
            return ApiResponse::forbidden($e->getMessage());
        }

        if ($e instanceof ModelNotFoundException) {
            return ApiResponse::notFound(
                $this->getModelNotFoundMessage($e)
            );
        }

        if ($e instanceof TooManyRequestsHttpException || $e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
            $retryAfter = $this->getRetryAfter($e);

            return ApiResponse::throttle($retryAfter);
        }

        if ($e instanceof NotFoundHttpException) {
            return ApiResponse::notFound($e->getMessage() ?: 'The requested resource was not found.');
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return ApiResponse::error(
                $e->getMessage() ?: 'The method is not allowed for the requested route.',
                405,
                'METHOD_NOT_ALLOWED',
            );
        }

        $statusCode = $this->exceptionMapper->getStatusCode($e);
        $message = $this->exceptionMapper->shouldHideStackTraces()
            ? 'An error occurred while processing your request.'
            : $e->getMessage();

        if ($this->exceptionMapper->shouldLog($e)) {
            $this->logException($e);
        }

        return ApiResponse::error($message, $statusCode);
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     */
    protected function unauthenticated($request, AuthenticationException $exception): JsonResponse
    {
        return ApiResponse::unauthorized($exception->getMessage());
    }

    /**
     * Get the model not found message.
     *
     * @param  \Database\Eloquent\ModelNotFoundException  $e
     */
    protected function getModelNotFoundMessage(ModelNotFoundException $e): string
    {
        $model = class_basename($e->getModel());

        return "No {$model} found with the given criteria.";
    }

    /**
     * Get the retry after value from exception.
     *
     * @param  \Throwable  $e
     */
    protected function getRetryAfter(\Throwable $e): int
    {
        if (method_exists($e, 'getRetryAfter')) {
            return (int) $e->getRetryAfter();
        }

        if (method_exists($e, 'getHeaders') && isset($e->getHeaders()['Retry-After'])) {
            return (int) $e->getHeaders()['Retry-After'];
        }

        return 60;
    }

    /**
     * Log the exception.
     *
     * @param  \Throwable  $e
     */
    protected function logException(\Throwable $e): void
    {
        if (config('mts-api.exception_handling.log_exceptions', true)) {
            report($e);
        }
    }
}
