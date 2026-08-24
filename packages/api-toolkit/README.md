# MTS Laravel API Toolkit

Standardized enterprise API responses, errors and API architecture for Laravel.

## Requirements

- PHP 8.3+
- Laravel 13.x

## Installation

```bash
composer require magetech/laravel-api-toolkit
php artisan mts:api:install
```

## Quick Start

### Basic Usage

```php
use MageTech\ApiToolkit\Support\Facades\ApiResponse;

// Success response
return ApiResponse::success(
    data: $users,
    message: 'Users retrieved successfully'
);

// Created response
return ApiResponse::created(
    data: $user,
    message: 'User created successfully'
);

// Error response
return ApiResponse::error(
    message: 'Bad request',
    code: 400
);
```

### Response Format

```json
{
    "success": true,
    "message": "Users retrieved successfully",
    "data": [...],
    "meta": {
        "request_id": "req_abc123def456",
        "correlation_id": "corr_xyz789",
        "api_version": "v1",
        "timestamp": "2026-08-24T12:00:00Z"
    }
}
```

## API Reference

### Success Responses

```php
// Success (200)
ApiResponse::success($data, $message);

// Created (201)
ApiResponse::created($data, $message);

// No Content (204)
ApiResponse::noContent($message);
```

### Error Responses

```php
// Bad Request (400)
ApiResponse::error('Bad request', 400);

// Unauthorized (401)
ApiResponse::unauthorized('Unauthorized');

// Forbidden (403)
ApiResponse::forbidden('Forbidden');

// Not Found (404)
ApiResponse::notFound('Not Found');

// Conflict (409)
ApiResponse::conflict('Conflict');

// Rate Limited (429)
ApiResponse::throttle(60, 'Too Many Requests');

// Server Error (500)
ApiResponse::serverError('Internal Server Error');
```

### Pagination

```php
$users = User::paginate(15);

return ApiResponse::paginated($users, 'Users retrieved');
```

### Resource Responses

```php
use Tests\Fixtures\UserResource;

return ApiResponse::resource(
    new UserResource($user),
    'User retrieved'
);
```

### Collection Responses

```php
return ApiResponse::collection(
    UserResource::collection($users),
    'Users retrieved'
);
```

### Validation Errors

```php
use Illuminate\Validation\ValidationException;

try {
    // Validation logic
} catch (ValidationException $e) {
    return ApiResponse::validation($e);
}
```

## Configuration

### Config File

```php
// config/mts-api.php
return [
    'response' => [
        'envelope' => true,
        'include_request_id' => true,
        'include_timestamp' => true,
        'include_api_version' => true,
    ],
    'versioning' => [
        'enabled' => true,
        'default' => 'v1',
        'header' => 'X-API-Version',
    ],
    'request_id' => [
        'header' => 'X-Request-ID',
        'prefix' => 'req_',
    ],
    // ...
];
```

## Middleware

### Request ID Middleware

Adds a unique request ID to every request.

```php
// app/Http/Kernel.php
protected $middlewareAliases = [
    'mts.request_id' => \MageTech\ApiToolkit\Middleware\MtsRequestIdMiddleware::class,
];
```

### Response Middleware

Adds correlation ID, API version, and other headers.

```php
// app/Http/Kernel.php
protected $middlewareAliases = [
    'mts.response' => \MageTech\ApiToolkit\Middleware\MtsApiResponseMiddleware::class,
];
```

## Exception Handling

The package automatically handles exceptions and returns API responses.

### Supported Exceptions

| Exception | Status Code | Error Code |
|-----------|-------------|------------|
| ValidationException | 422 | VALIDATION_ERROR |
| AuthenticationException | 401 | UNAUTHORIZED |
| AuthorizationException | 403 | FORBIDDEN |
| ModelNotFoundException | 404 | NOT_FOUND |
| ThrottleRequestsException | 429 | RATE_LIMITED |

### Custom Exceptions

```php
use MageTech\ApiToolkit\ApiException;

throw ApiException::notFound('User not found');
throw ApiException::validation(['email' => ['Required']]);
throw ApiException::forbidden('Insufficient permissions');
```

## Controller Traits

```php
use MageTech\ApiToolkit\Concerns\HasApiResponse;

class UserController extends Controller
{
    use HasApiResponse;

    public function index()
    {
        $users = User::all();

        return $this->apiSuccess($users, 'Users retrieved');
    }

    public function store(Request $request)
    {
        $user = User::create($request->validated());

        return $this->apiCreated($user, 'User created');
    }
}
```

## Helper Functions

```php
// Success response
api_success($data, $message);

// Error response
api_error($message, $code);

// Created response
api_created($data, $message);

// No content response
api_no_content($message);

// Paginated response
api_paginated($paginator, $message);

// Unauthorized response
api_unauthorized($message);

// Forbidden response
api_forbidden($message);

// Not found response
api_not_found($message);

// Validation response
api_validation($exception);

// Throttle response
api_throttle($retryAfter);

// Server error response
api_server_error($message);

// Generate IDs
$requestId = generate_request_id();
$correlationId = generate_correlation_id();
```

## API Versioning

### URL Prefix (Default)

```
/api/v1/users
/api/v2/users
```

### Header Versioning

```
GET /api/users
X-API-Version: v1
```

## Headers

| Header | Description |
|--------|-------------|
| X-Request-ID | Unique request identifier |
| X-Correlation-ID | Correlation ID for distributed tracing |
| X-API-Version | API version |
| X-RateLimit-Limit | Rate limit maximum |
| X-RateLimit-Remaining | Remaining requests |
| X-RateLimit-Reset | Reset timestamp |

## Testing

```bash
# Run all tests
vendor/bin/pest

# Run unit tests only
vendor/bin/pest --testsuite=Unit

# Run feature tests only
vendor/bin/pest --testsuite=Feature

# Run integration tests only
vendor/bin/pest --testsuite=Integration
```

## Code Style

```bash
# Check code style
vendor/bin/pint --test

# Fix code style
vendor/bin/pint
```

## Static Analysis

```bash
# Run PHPStan
vendor/bin/phpstan analyse
```

## License

MIT License. See [LICENSE](LICENSE) for details.
