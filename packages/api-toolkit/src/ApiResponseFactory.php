<?php

declare(strict_types=1);

namespace MageTech\ApiToolkit;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use MageTech\ApiToolkit\DTOs\ErrorData;
use MageTech\ApiToolkit\DTOs\PaginationData;
use MageTech\ApiToolkit\DTOs\ResponseMetadata;
use MageTech\ApiToolkit\DTOs\ValidationErrorData;

class ApiResponseFactory
{
    protected ?Request $request;

    protected bool $envelope = true;

    protected bool $includeRequestId = true;

    protected bool $includeTimestamp = true;

    protected bool $includeApiVersion = true;

    public function __construct(?Request $request = null)
    {
        $this->request = $request ?? request();
        $this->loadConfig();
    }

    /**
     * Load configuration settings.
     */
    protected function loadConfig(): void
    {
        $this->envelope = config('mts-api.response.envelope', true);
        $this->includeRequestId = config('mts-api.response.include_request_id', true);
        $this->includeTimestamp = config('mts-api.response.include_timestamp', true);
        $this->includeApiVersion = config('mts-api.response.include_api_version', true);
    }

    /**
     * Create a success response.
     *
     * @param  mixed  $data
     * @param  string  $message
     * @param  int  $code
     */
    public function success(mixed $data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return $this->buildResponse(
            success: true,
            message: $message,
            data: $data,
            code: $code,
        );
    }

    /**
     * Create an error response.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  string|null  $errorCode
     * @param  mixed  $data
     */
    public function error(string $message, int $code = 400, ?string $errorCode = null, mixed $data = null): JsonResponse
    {
        $errorData = new ErrorData(
            code: $errorCode ?? $this->guessErrorCode($code),
            type: $this->guessErrorType($code),
            message: $message,
        );

        return $this->buildResponse(
            success: false,
            message: $message,
            data: $data,
            code: $code,
            error: $errorData->toArray(),
        );
    }

    /**
     * Create a created response.
     *
     * @param  mixed  $data
     * @param  string  $message
     */
    public function created(mixed $data = null, string $message = 'Created'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    /**
     * Create a no content response.
     *
     * @param  string  $message
     */
    public function noContent(string $message = 'No Content'): JsonResponse
    {
        return $this->success(null, $message, 204);
    }

    /**
     * Create a paginated response.
     *
     * @param  LengthAwarePaginator  $paginator
     * @param  string  $message
     */
    public function paginated(LengthAwarePaginator $paginator, string $message = 'Success'): JsonResponse
    {
        $paginationData = PaginationData::fromPaginator($paginator);

        return $this->buildResponse(
            success: true,
            message: $message,
            data: $paginator->items(),
            code: 200,
            pagination: $paginationData->toArray(),
        );
    }

    /**
     * Create a resource response.
     *
     * @param  mixed  $resource
     * @param  string  $message
     * @param  int  $code
     */
    public function resource(mixed $resource, string $message = 'Success', int $code = 200): JsonResponse
    {
        $data = $resource instanceof \Illuminate\Http\Resources\Json\JsonResource
            ? $resource->resolve()
            : $resource;

        return $this->success($data, $message, $code);
    }

    /**
     * Create a collection response.
     *
     * @param  array|iterable  $collection
     * @param  string  $message
     * @param  int  $code
     */
    public function collection(array|iterable $collection, string $message = 'Success', int $code = 200): JsonResponse
    {
        $data = is_array($collection) ? $collection : iterator_to_array($collection);

        return $this->success($data, $message, $code);
    }

    /**
     * Create a validation error response.
     *
     * @param  \Illuminate\Validation\ValidationException  $exception
     */
    public function validation(\Illuminate\Validation\ValidationException $exception): JsonResponse
    {
        $validationError = ValidationErrorData::fromException($exception);

        return $this->buildResponse(
            success: false,
            message: $validationError->message,
            data: null,
            code: 422,
            error: $validationError->toArray(),
        );
    }

    /**
     * Create an unauthorized response.
     *
     * @param  string  $message
     */
    public function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, 401, 'UNAUTHORIZED');
    }

    /**
     * Create a forbidden response.
     *
     * @param  string  $message
     */
    public function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, 403, 'FORBIDDEN');
    }

    /**
     * Create a not found response.
     *
     * @param  string  $message
     */
    public function notFound(string $message = 'Not Found'): JsonResponse
    {
        return $this->error($message, 404, 'NOT_FOUND');
    }

    /**
     * Create a conflict response.
     *
     * @param  string  $message
     */
    public function conflict(string $message = 'Conflict'): JsonResponse
    {
        return $this->error($message, 409, 'CONFLICT');
    }

    /**
     * Create a rate limit response.
     *
     * @param  int  $retryAfter
     * @param  string  $message
     */
    public function throttle(int $retryAfter = 60, string $message = 'Too Many Requests'): JsonResponse
    {
        return $this->buildResponse(
            success: false,
            message: $message,
            data: null,
            code: 429,
            error: ErrorData::rateLimited($message)->toArray(),
            headers: [
                'Retry-After' => $retryAfter,
                'X-RateLimit-Limit' => config('mts-api.pagination.max_per_page', 60),
                'X-RateLimit-Remaining' => 0,
                'X-RateLimit-Reset' => now()->addSeconds($retryAfter)->timestamp,
            ],
        );
    }

    /**
     * Create a server error response.
     *
     * @param  string  $message
     */
    public function serverError(string $message = 'Internal Server Error'): JsonResponse
    {
        return $this->error($message, 500, 'SERVER_ERROR');
    }

    /**
     * Create a custom response.
     *
     * @param  int  $code
     * @param  string  $message
     * @param  mixed  $data
     * @param  string|null  $errorCode
     */
    public function custom(int $code, string $message, mixed $data = null, ?string $errorCode = null): JsonResponse
    {
        if ($code >= 200 && $code < 300) {
            return $this->success($data, $message, $code);
        }

        return $this->error($message, $code, $errorCode, $data);
    }

    /**
     * Build the response.
     *
     * @param  bool  $success
     * @param  string  $message
     * @param  mixed  $data
     * @param  int  $code
     * @param  array|null  $error
     * @param  array|null  $pagination
     * @param  array  $headers
     */
    protected function buildResponse(
        bool $success,
        string $message,
        mixed $data,
        int $code,
        ?array $error = null,
        ?array $pagination = null,
        array $headers = [],
    ): JsonResponse {
        $metadata = $this->buildMetadata($pagination);

        if ($this->envelope) {
            $response = [
                'success' => $success,
                'message' => $message,
                'data' => $data,
                'meta' => $metadata->toArray(),
            ];

            if ($error !== null) {
                $response['error'] = $error;
            }
        } else {
            $response = $data ?? [];

            if ($error !== null) {
                $response['error'] = $error;
            }
        }

        $headers = array_merge($headers, $this->buildHeaders($metadata));

        return response()->json($response, $code, $headers);
    }

    /**
     * Build response metadata.
     *
     * @param  array|null  $pagination
     */
    protected function buildMetadata(?array $pagination = null): ResponseMetadata
    {
        return new ResponseMetadata(
            requestId: $this->includeRequestId ? $this->getRequestId() : null,
            correlationId: $this->includeRequestId ? $this->getCorrelationId() : null,
            apiVersion: $this->includeApiVersion ? $this->getApiVersion() : null,
            timestamp: $this->includeTimestamp ? now()->toIso8601String() : null,
            pagination: $pagination ? new PaginationData(
                currentPage: $pagination['current_page'],
                perPage: $pagination['per_page'],
                total: $pagination['total'],
                lastPage: $pagination['last_page'],
                links: $pagination['links'],
            ) : null,
        );
    }

    /**
     * Build response headers.
     *
     * @param  ResponseMetadata  $metadata
     * @return array<string, string>
     */
    protected function buildHeaders(ResponseMetadata $metadata): array
    {
        $headers = [];

        if ($metadata->requestId !== null) {
            $headers['X-Request-ID'] = $metadata->requestId;
        }

        if ($metadata->correlationId !== null) {
            $headers['X-Correlation-ID'] = $metadata->correlationId;
        }

        if ($metadata->apiVersion !== null) {
            $headers['X-API-Version'] = $metadata->apiVersion;
        }

        $exposedHeaders = config('mts-api.security.expose_headers', []);
        if (! empty($exposedHeaders)) {
            $headers['Access-Control-Expose-Headers'] = implode(', ', $exposedHeaders);
        }

        return $headers;
    }

    /**
     * Get the request ID.
     */
    protected function getRequestId(): string
    {
        $header = config('mts-api.request_id.header', 'X-Request-ID');
        $requestId = $this->request->header($header);

        if ($requestId !== null && $requestId !== '') {
            return $requestId;
        }

        if (config('mts-api.request_id.generate_if_missing', true)) {
            return $this->generateId(
                config('mts-api.request_id.prefix', 'req_'),
                config('mts-api.request_id.length', 32),
            );
        }

        return '';
    }

    /**
     * Get the correlation ID.
     */
    protected function getCorrelationId(): string
    {
        $header = config('mts-api.correlation_id.header', 'X-Correlation-ID');
        $correlationId = $this->request->header($header);

        if ($correlationId !== null && $correlationId !== '') {
            return $correlationId;
        }

        if (config('mts-api.correlation_id.generate_if_missing', true)) {
            return $this->generateId(
                config('mts-api.correlation_id.prefix', 'corr_'),
            );
        }

        return '';
    }

    /**
     * Get the API version.
     */
    protected function getApiVersion(): string
    {
        $header = config('mts-api.versioning.header', 'X-API-Version');
        $version = $this->request->header($header);

        if ($version !== null && $version !== '') {
            return $version;
        }

        $route = $this->request->route();
        if ($route !== null && isset($route->parameters()['version'])) {
            return $route->parameters()['version'];
        }

        return config('mts-api.versioning.default', 'v1');
    }

    /**
     * Generate a unique ID.
     *
     * @param  string  $prefix
     * @param  int  $length
     */
    protected function generateId(string $prefix = '', int $length = 32): string
    {
        $random = Str::random($length);

        return $prefix . $random;
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
            $code === 408 => 'REQUEST_TIMEOUT',
            $code === 409 => 'CONFLICT',
            $code === 413 => 'PAYLOAD_TOO_LARGE',
            $code === 415 => 'UNSUPPORTED_MEDIA_TYPE',
            $code === 422 => 'UNPROCESSABLE_ENTITY',
            $code === 429 => 'TOO_MANY_REQUESTS',
            $code === 500 => 'INTERNAL_ERROR',
            $code === 501 => 'NOT_IMPLEMENTED',
            $code === 502 => 'BAD_GATEWAY',
            $code === 503 => 'SERVICE_UNAVAILABLE',
            $code === 504 => 'GATEWAY_TIMEOUT',
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
