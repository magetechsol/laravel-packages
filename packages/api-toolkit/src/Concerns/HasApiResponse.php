<?php

declare(strict_types=1);

namespace MageTech\ApiToolkit\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use MageTech\ApiToolkit\ApiResponse;

trait HasApiResponse
{
    /**
     * Get the ApiResponse instance.
     */
    protected function apiResponse(): ApiResponse
    {
        return new ApiResponse();
    }

    /**
     * Return a success response.
     *
     * @param  mixed  $data
     * @param  string  $message
     * @param  int  $code
     */
    protected function apiSuccess(mixed $data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return ApiResponse::success($data, $message, $code);
    }

    /**
     * Return an error response.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  string|null  $errorCode
     * @param  mixed  $data
     */
    protected function apiError(string $message, int $code = 400, ?string $errorCode = null, mixed $data = null): JsonResponse
    {
        return ApiResponse::error($message, $code, $errorCode, $data);
    }

    /**
     * Return a created response.
     *
     * @param  mixed  $data
     * @param  string  $message
     */
    protected function apiCreated(mixed $data = null, string $message = 'Created'): JsonResponse
    {
        return ApiResponse::created($data, $message);
    }

    /**
     * Return a no content response.
     *
     * @param  string  $message
     */
    protected function apiNoContent(string $message = 'No Content'): JsonResponse
    {
        return ApiResponse::noContent($message);
    }

    /**
     * Return a paginated response.
     *
     * @param  LengthAwarePaginator  $paginator
     * @param  string  $message
     */
    protected function apiPaginated(LengthAwarePaginator $paginator, string $message = 'Success'): JsonResponse
    {
        return ApiResponse::paginated($paginator, $message);
    }

    /**
     * Return a validation error response.
     *
     * @param  ValidationException  $exception
     */
    protected function apiValidation(ValidationException $exception): JsonResponse
    {
        return ApiResponse::validation($exception);
    }

    /**
     * Return an unauthorized response.
     *
     * @param  string  $message
     */
    protected function apiUnauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return ApiResponse::unauthorized($message);
    }

    /**
     * Return a forbidden response.
     *
     * @param  string  $message
     */
    protected function apiForbidden(string $message = 'Forbidden'): JsonResponse
    {
        return ApiResponse::forbidden($message);
    }

    /**
     * Return a not found response.
     *
     * @param  string  $message
     */
    protected function apiNotFound(string $message = 'Not Found'): JsonResponse
    {
        return ApiResponse::notFound($message);
    }

    /**
     * Return a conflict response.
     *
     * @param  string  $message
     */
    protected function apiConflict(string $message = 'Conflict'): JsonResponse
    {
        return ApiResponse::conflict($message);
    }

    /**
     * Return a rate limit response.
     *
     * @param  int  $retryAfter
     * @param  string  $message
     */
    protected function apiThrottle(int $retryAfter = 60, string $message = 'Too Many Requests'): JsonResponse
    {
        return ApiResponse::throttle($retryAfter, $message);
    }

    /**
     * Return a server error response.
     *
     * @param  string  $message
     */
    protected function apiServerError(string $message = 'Internal Server Error'): JsonResponse
    {
        return ApiResponse::serverError($message);
    }
}
