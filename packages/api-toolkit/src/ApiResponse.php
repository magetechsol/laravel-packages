<?php

declare(strict_types=1);

namespace MageTech\ApiToolkit;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use MageTech\ApiToolkit\Contracts\ApiResponseContract;

class ApiResponse implements ApiResponseContract
{
    protected static ?ApiResponseFactory $factory = null;

    /**
     * Set the factory instance.
     *
     * @param  ApiResponseFactory  $factory
     */
    public static function setFactory(ApiResponseFactory $factory): void
    {
        static::$factory = $factory;
    }

    /**
     * Get the factory instance.
     */
    public static function getFactory(): ApiResponseFactory
    {
        if (static::$factory === null) {
            static::$factory = new ApiResponseFactory();
        }

        return static::$factory;
    }

    /**
     * Create a success response.
     *
     * @param  mixed  $data
     * @param  string  $message
     * @param  int  $code
     */
    public static function success(mixed $data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return static::getFactory()->success($data, $message, $code);
    }

    /**
     * Create an error response.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  string|null  $errorCode
     * @param  mixed  $data
     */
    public static function error(string $message, int $code = 400, ?string $errorCode = null, mixed $data = null): JsonResponse
    {
        return static::getFactory()->error($message, $code, $errorCode, $data);
    }

    /**
     * Create a created response.
     *
     * @param  mixed  $data
     * @param  string  $message
     */
    public static function created(mixed $data = null, string $message = 'Created'): JsonResponse
    {
        return static::getFactory()->created($data, $message);
    }

    /**
     * Create a no content response.
     *
     * @param  string  $message
     */
    public static function noContent(string $message = 'No Content'): JsonResponse
    {
        return static::getFactory()->noContent($message);
    }

    /**
     * Create a paginated response.
     *
     * @param  LengthAwarePaginator  $paginator
     * @param  string  $message
     */
    public static function paginated(LengthAwarePaginator $paginator, string $message = 'Success'): JsonResponse
    {
        return static::getFactory()->paginated($paginator, $message);
    }

    /**
     * Create a resource response.
     *
     * @param  mixed  $resource
     * @param  string  $message
     * @param  int  $code
     */
    public static function resource(mixed $resource, string $message = 'Success', int $code = 200): JsonResponse
    {
        return static::getFactory()->resource($resource, $message, $code);
    }

    /**
     * Create a collection response.
     *
     * @param  array|iterable  $collection
     * @param  string  $message
     * @param  int  $code
     */
    public static function collection(array|iterable $collection, string $message = 'Success', int $code = 200): JsonResponse
    {
        return static::getFactory()->collection($collection, $message, $code);
    }

    /**
     * Create a validation error response.
     *
     * @param  ValidationException  $exception
     */
    public static function validation(ValidationException $exception): JsonResponse
    {
        return static::getFactory()->validation($exception);
    }

    /**
     * Create an unauthorized response.
     *
     * @param  string  $message
     */
    public static function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return static::getFactory()->unauthorized($message);
    }

    /**
     * Create a forbidden response.
     *
     * @param  string  $message
     */
    public static function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return static::getFactory()->forbidden($message);
    }

    /**
     * Create a not found response.
     *
     * @param  string  $message
     */
    public static function notFound(string $message = 'Not Found'): JsonResponse
    {
        return static::getFactory()->notFound($message);
    }

    /**
     * Create a conflict response.
     *
     * @param  string  $message
     */
    public static function conflict(string $message = 'Conflict'): JsonResponse
    {
        return static::getFactory()->conflict($message);
    }

    /**
     * Create a rate limit response.
     *
     * @param  int  $retryAfter
     * @param  string  $message
     */
    public static function throttle(int $retryAfter = 60, string $message = 'Too Many Requests'): JsonResponse
    {
        return static::getFactory()->throttle($retryAfter, $message);
    }

    /**
     * Create a server error response.
     *
     * @param  string  $message
     */
    public static function serverError(string $message = 'Internal Server Error'): JsonResponse
    {
        return static::getFactory()->serverError($message);
    }

    /**
     * Create a custom response.
     *
     * @param  int  $code
     * @param  string  $message
     * @param  mixed  $data
     * @param  string|null  $errorCode
     */
    public static function custom(int $code, string $message, mixed $data = null, ?string $errorCode = null): JsonResponse
    {
        return static::getFactory()->custom($code, $message, $data, $errorCode);
    }

    /**
     * Handle dynamic method calls to the factory.
     *
     * @param  string  $method
     * @param  array  $parameters
     */
    public static function __callStatic(string $method, array $parameters): mixed
    {
        return static::getFactory()->$method(...$parameters);
    }
}
