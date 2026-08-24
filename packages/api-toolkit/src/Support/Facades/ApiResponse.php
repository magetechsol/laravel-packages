<?php

declare(strict_types=1);

namespace MageTech\ApiToolkit\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MageTech\ApiToolkit\ApiResponse as ApiResponseClass;

/**
 * @method static \Illuminate\Http\JsonResponse success(mixed $data = null, string $message = 'Success', int $code = 200)
 * @method static \Illuminate\Http\JsonResponse error(string $message, int $code = 400, ?string $errorCode = null, mixed $data = null)
 * @method static \Illuminate\Http\JsonResponse created(mixed $data = null, string $message = 'Created')
 * @method static \Illuminate\Http\JsonResponse noContent(string $message = 'No Content')
 * @method static \Illuminate\Http\JsonResponse paginated(\Illuminate\Pagination\LengthAwarePaginator $paginator, string $message = 'Success')
 * @method static \Illuminate\Http\JsonResponse resource(mixed $resource, string $message = 'Success', int $code = 200)
 * @method static \Illuminate\Http\JsonResponse collection(array|iterable $collection, string $message = 'Success', int $code = 200)
 * @method static \Illuminate\Http\JsonResponse validation(\Illuminate\Validation\ValidationException $exception)
 * @method static \Illuminate\Http\JsonResponse unauthorized(string $message = 'Unauthorized')
 * @method static \Illuminate\Http\JsonResponse forbidden(string $message = 'Forbidden')
 * @method static \Illuminate\Http\JsonResponse notFound(string $message = 'Not Found')
 * @method static \Illuminate\Http\JsonResponse conflict(string $message = 'Conflict')
 * @method static \Illuminate\Http\JsonResponse throttle(int $retryAfter = 60, string $message = 'Too Many Requests')
 * @method static \Illuminate\Http\JsonResponse serverError(string $message = 'Internal Server Error')
 * @method static \Illuminate\Http\JsonResponse custom(int $code, string $message, mixed $data = null, ?string $errorCode = null)
 *
 * @see \MageTech\ApiToolkit\ApiResponse
 */
class ApiResponse extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return ApiResponseClass::class;
    }
}
