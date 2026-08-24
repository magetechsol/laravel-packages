<?php

declare(strict_types=1);

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use MageTech\ApiToolkit\ApiResponse;

if (! function_exists('api_success')) {
    /**
     * Return a success response.
     *
     * @param  mixed  $data
     * @param  string  $message
     */
    function api_success(mixed $data = null, string $message = 'Success'): JsonResponse
    {
        return ApiResponse::success($data, $message);
    }
}

if (! function_exists('api_error')) {
    /**
     * Return an error response.
     *
     * @param  string  $message
     * @param  int  $code
     */
    function api_error(string $message, int $code = 400): JsonResponse
    {
        return ApiResponse::error($message, $code);
    }
}

if (! function_exists('api_created')) {
    /**
     * Return a created response.
     *
     * @param  mixed  $data
     * @param  string  $message
     */
    function api_created(mixed $data = null, string $message = 'Created'): JsonResponse
    {
        return ApiResponse::created($data, $message);
    }
}

if (! function_exists('api_no_content')) {
    /**
     * Return a no content response.
     *
     * @param  string  $message
     */
    function api_no_content(string $message = 'No Content'): JsonResponse
    {
        return ApiResponse::noContent($message);
    }
}

if (! function_exists('api_paginated')) {
    /**
     * Return a paginated response.
     *
     * @param  LengthAwarePaginator  $paginator
     * @param  string  $message
     */
    function api_paginated(LengthAwarePaginator $paginator, string $message = 'Success'): JsonResponse
    {
        return ApiResponse::paginated($paginator, $message);
    }
}

if (! function_exists('api_unauthorized')) {
    /**
     * Return an unauthorized response.
     *
     * @param  string  $message
     */
    function api_unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return ApiResponse::unauthorized($message);
    }
}

if (! function_exists('api_forbidden')) {
    /**
     * Return a forbidden response.
     *
     * @param  string  $message
     */
    function api_forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return ApiResponse::forbidden($message);
    }
}

if (! function_exists('api_not_found')) {
    /**
     * Return a not found response.
     *
     * @param  string  $message
     */
    function api_not_found(string $message = 'Not Found'): JsonResponse
    {
        return ApiResponse::notFound($message);
    }
}

if (! function_exists('api_validation')) {
    /**
     * Return a validation error response.
     *
     * @param  ValidationException  $e
     */
    function api_validation(ValidationException $e): JsonResponse
    {
        return ApiResponse::validation($e);
    }
}

if (! function_exists('api_throttle')) {
    /**
     * Return a rate limit response.
     *
     * @param  int  $retryAfter
     */
    function api_throttle(int $retryAfter = 60): JsonResponse
    {
        return ApiResponse::throttle($retryAfter);
    }
}

if (! function_exists('api_server_error')) {
    /**
     * Return a server error response.
     *
     * @param  string  $message
     */
    function api_server_error(string $message = 'Internal Server Error'): JsonResponse
    {
        return ApiResponse::serverError($message);
    }
}

if (! function_exists('generate_request_id')) {
    /**
     * Generate a unique request ID.
     */
    function generate_request_id(): string
    {
        $prefix = config('mts-api.request_id.prefix', 'req_');
        $length = config('mts-api.request_id.length', 32);

        return $prefix . Str::random($length);
    }
}

if (! function_exists('generate_correlation_id')) {
    /**
     * Generate a unique correlation ID.
     */
    function generate_correlation_id(): string
    {
        $prefix = config('mts-api.correlation_id.prefix', 'corr_');

        return $prefix . Str::random(32);
    }
}
