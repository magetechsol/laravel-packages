<?php

declare(strict_types=1);

namespace MageTech\ApiToolkit\Enums;

enum ErrorCode: string
{
    case VALIDATION_ERROR = 'VALIDATION_ERROR';
    case UNAUTHORIZED = 'UNAUTHORIZED';
    case FORBIDDEN = 'FORBIDDEN';
    case NOT_FOUND = 'NOT_FOUND';
    case CONFLICT = 'CONFLICT';
    case RATE_LIMITED = 'RATE_LIMITED';
    case SERVER_ERROR = 'SERVER_ERROR';
    case SERVICE_UNAVAILABLE = 'SERVICE_UNAVAILABLE';
    case BAD_REQUEST = 'BAD_REQUEST';
    case METHOD_NOT_ALLOWED = 'METHOD_NOT_ALLOWED';
    case REQUEST_TIMEOUT = 'REQUEST_TIMEOUT';
    case PAYLOAD_TOO_LARGE = 'PAYLOAD_TOO_LARGE';
    case UNSUPPORTED_MEDIA_TYPE = 'UNSUPPORTED_MEDIA_TYPE';
    case UNPROCESSABLE_ENTITY = 'UNPROCESSABLE_ENTITY';
    case TOO_MANY_REQUESTS = 'TOO_MANY_REQUESTS';
    case INTERNAL_ERROR = 'INTERNAL_ERROR';
    case NOT_IMPLEMENTED = 'NOT_IMPLEMENTED';
    case BAD_GATEWAY = 'BAD_GATEWAY';
    case GATEWAY_TIMEOUT = 'GATEWAY_TIMEOUT';

    /**
     * Get the HTTP status code for this error.
     */
    public function statusCode(): int
    {
        return match ($this) {
            self::VALIDATION_ERROR => 422,
            self::UNAUTHORIZED => 401,
            self::FORBIDDEN => 403,
            self::NOT_FOUND => 404,
            self::CONFLICT => 409,
            self::RATE_LIMITED, self::TOO_MANY_REQUESTS => 429,
            self::SERVER_ERROR, self::INTERNAL_ERROR => 500,
            self::SERVICE_UNAVAILABLE => 503,
            self::BAD_REQUEST => 400,
            self::METHOD_NOT_ALLOWED => 405,
            self::REQUEST_TIMEOUT => 408,
            self::PAYLOAD_TOO_LARGE => 413,
            self::UNSUPPORTED_MEDIA_TYPE => 415,
            self::UNPROCESSABLE_ENTITY => 422,
            self::NOT_IMPLEMENTED => 501,
            self::BAD_GATEWAY => 502,
            self::GATEWAY_TIMEOUT => 504,
        };
    }

    /**
     * Get the error type for this error code.
     */
    public function type(): string
    {
        return match ($this) {
            self::VALIDATION_ERROR => 'validation',
            self::UNAUTHORIZED => 'authentication',
            self::FORBIDDEN => 'authorization',
            self::NOT_FOUND => 'not_found',
            self::CONFLICT => 'conflict',
            self::RATE_LIMITED, self::TOO_MANY_REQUESTS => 'rate_limit',
            self::SERVER_ERROR, self::INTERNAL_ERROR, self::NOT_IMPLEMENTED => 'server',
            self::SERVICE_UNAVAILABLE, self::BAD_GATEWAY, self::GATEWAY_TIMEOUT => 'service',
            self::BAD_REQUEST => 'client',
            self::METHOD_NOT_ALLOWED, self::UNSUPPORTED_MEDIA_TYPE => 'client',
            self::REQUEST_TIMEOUT => 'timeout',
            self::PAYLOAD_TOO_LARGE => 'client',
            self::UNPROCESSABLE_ENTITY => 'validation',
        };
    }

    /**
     * Get the default message for this error code.
     */
    public function defaultMessage(): string
    {
        return match ($this) {
            self::VALIDATION_ERROR => 'The given data was invalid.',
            self::UNAUTHORIZED => 'Unauthenticated.',
            self::FORBIDDEN => 'Forbidden.',
            self::NOT_FOUND => 'The requested resource was not found.',
            self::CONFLICT => 'The request conflicts with the current state.',
            self::RATE_LIMITED, self::TOO_MANY_REQUESTS => 'Too many requests.',
            self::SERVER_ERROR, self::INTERNAL_ERROR => 'Internal server error.',
            self::SERVICE_UNAVAILABLE => 'Service unavailable.',
            self::BAD_REQUEST => 'Bad request.',
            self::METHOD_NOT_ALLOWED => 'Method not allowed.',
            self::REQUEST_TIMEOUT => 'Request timeout.',
            self::PAYLOAD_TOO_LARGE => 'Payload too large.',
            self::UNSUPPORTED_MEDIA_TYPE => 'Unsupported media type.',
            self::UNPROCESSABLE_ENTITY => 'Unprocessable entity.',
            self::NOT_IMPLEMENTED => 'Not implemented.',
            self::BAD_GATEWAY => 'Bad gateway.',
            self::GATEWAY_TIMEOUT => 'Gateway timeout.',
        };
    }
}
