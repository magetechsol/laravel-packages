<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Response Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how API responses are structured and formatted.
    |
    */

    'response' => [
        'envelope' => env('MTS_API_ENVELOPE', true),
        'format' => env('MTS_API_FORMAT', 'standard'),
        'include_request_id' => env('MTS_API_INCLUDE_REQUEST_ID', true),
        'include_timestamp' => env('MTS_API_INCLUDE_TIMESTAMP', true),
        'include_api_version' => env('MTS_API_INCLUDE_VERSION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Versioning Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how API versioning is handled.
    |
    */

    'versioning' => [
        'enabled' => env('MTS_API_VERSIONING_ENABLED', true),
        'default' => env('MTS_API_VERSION_DEFAULT', 'v1'),
        'header' => 'X-API-Version',
        'url_prefix' => env('MTS_API_URL_PREFIX', true),
        'parameter' => false,
        'header_allowed_versions' => ['v1', 'v2'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Request ID Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how request IDs are generated and tracked.
    |
    */

    'request_id' => [
        'header' => 'X-Request-ID',
        'prefix' => 'req_',
        'generate_if_missing' => true,
        'length' => 32,
    ],

    /*
    |--------------------------------------------------------------------------
    | Correlation ID Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how correlation IDs are handled for distributed tracing.
    |
    */

    'correlation_id' => [
        'header' => 'X-Correlation-ID',
        'prefix' => 'corr_',
        'generate_if_missing' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination Configuration
    |--------------------------------------------------------------------------
    |
    | Configure default pagination settings.
    |
    */

    'pagination' => [
        'default_per_page' => env('MTS_API_DEFAULT_PER_PAGE', 15),
        'max_per_page' => env('MTS_API_MAX_PER_PAGE', 100),
        'per_page_parameter' => 'per_page',
        'page_parameter' => 'page',
    ],

    /*
    |--------------------------------------------------------------------------
    | Exception Handling Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how exceptions are handled and mapped to HTTP responses.
    |
    */

    'exception_handling' => [
        'enabled' => env('MTS_API_EXCEPTION_HANDLING', true),
        'map_exceptions' => true,
        'hide_stack_traces' => env('APP_DEBUG', false) === false,
        'log_exceptions' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Exception Map
    |--------------------------------------------------------------------------
    |
    | Map exception classes to HTTP status codes.
    |
    */

    'exception_map' => [
        \Illuminate\Validation\ValidationException::class => 422,
        \Illuminate\Auth\AuthenticationException::class => 401,
        \Illuminate\Auth\Access\AuthorizationException::class => 403,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class => 404,
        \Illuminate\Http\Exceptions\ThrottleRequestsException::class => 429,
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class => 404,
        \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class => 405,
        \Symfony\Component\HttpKernel\Exception\ConflictHttpException::class => 409,
        \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException::class => 429,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Configure security headers and CORS settings.
    |
    */

    'security' => [
        'expose_headers' => [
            'X-Request-ID',
            'X-Correlation-ID',
            'X-API-Version',
            'X-RateLimit-Limit',
            'X-RateLimit-Remaining',
            'X-RateLimit-Reset',
        ],
        'cors' => [
            'allowed_origins' => ['*'],
            'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
            'allowed_headers' => ['*'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure request and response logging.
    |
    */

    'logging' => [
        'enabled' => env('MTS_API_LOGGING', true),
        'log_requests' => true,
        'log_responses' => true,
        'log_level' => 'info',
        'exclude_paths' => ['/health', '/ping'],
    ],

];
