<?php

declare(strict_types=1);

namespace MageTech\ApiToolkit\Contracts;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

interface ApiResponseContract
{
    /**
     * Return a success response.
     *
     * @param  mixed  $data
     * @param  string  $message
     * @param  int  $code
     */
    public static function success(mixed $data = null, string $message = 'Success', int $code = 200): JsonResponse;

    /**
     * Return an error response.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  string|null  $errorCode
     * @param  mixed  $data
     */
    public static function error(string $message, int $code = 400, ?string $errorCode = null, mixed $data = null): JsonResponse;

    /**
     * Return a created response (201).
     *
     * @param  mixed  $data
     * @param  string  $message
     */
    public static function created(mixed $data = null, string $message = 'Created'): JsonResponse;

    /**
     * Return a no content response (204).
     *
     * @param  string  $message
     */
    public static function noContent(string $message = 'No Content'): JsonResponse;

    /**
     * Return a paginated response.
     *
     * @param  LengthAwarePaginator  $paginator
     * @param  string  $message
     */
    public static function paginated(LengthAwarePaginator $paginator, string $message = 'Success'): JsonResponse;

    /**
     * Return a resource response.
     *
     * @param  JsonResource  $resource
     * @param  string  $message
     * @param  int  $code
     */
    public static function resource(JsonResource $resource, string $message = 'Success', int $code = 200): JsonResponse;

    /**
     * Return a collection response.
     *
     * @param  array|iterable  $collection
     * @param  string  $message
     * @param  int  $code
     */
    public static function collection(array|iterable $collection, string $message = 'Success', int $code = 200): JsonResponse;

    /**
     * Return a validation error response.
     *
     * @param  ValidationException  $exception
     */
    public static function validation(ValidationException $exception): JsonResponse;

    /**
     * Return an unauthorized response (401).
     *
     * @param  string  $message
     */
    public static function unauthorized(string $message = 'Unauthorized'): JsonResponse;

    /**
     * Return a forbidden response (403).
     *
     * @param  string  $message
     */
    public static function forbidden(string $message = 'Forbidden'): JsonResponse;

    /**
     * Return a not found response (404).
     *
     * @param  string  $message
     */
    public static function notFound(string $message = 'Not Found'): JsonResponse;

    /**
     * Return a conflict response (409).
     *
     * @param  string  $message
     */
    public static function conflict(string $message = 'Conflict'): JsonResponse;

    /**
     * Return a rate limit response (429).
     *
     * @param  int  $retryAfter
     * @param  string  $message
     */
    public static function throttle(int $retryAfter = 60, string $message = 'Too Many Requests'): JsonResponse;

    /**
     * Return a server error response (500).
     *
     * @param  string  $message
     */
    public static function serverError(string $message = 'Internal Server Error'): JsonResponse;

    /**
     * Return a custom response.
     *
     * @param  int  $code
     * @param  string  $message
     * @param  mixed  $data
     * @param  string|null  $errorCode
     */
    public static function custom(int $code, string $message, mixed $data = null, ?string $errorCode = null): JsonResponse;
}
