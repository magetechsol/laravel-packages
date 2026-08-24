<?php

declare(strict_types=1);

use MageTech\ApiToolkit\Enums\ErrorCode;

test('error code returns correct status code', function () {
    expect(ErrorCode::VALIDATION_ERROR->statusCode())->toBe(422)
        ->and(ErrorCode::UNAUTHORIZED->statusCode())->toBe(401)
        ->and(ErrorCode::FORBIDDEN->statusCode())->toBe(403)
        ->and(ErrorCode::NOT_FOUND->statusCode())->toBe(404)
        ->and(ErrorCode::CONFLICT->statusCode())->toBe(409)
        ->and(ErrorCode::RATE_LIMITED->statusCode())->toBe(429)
        ->and(ErrorCode::SERVER_ERROR->statusCode())->toBe(500)
        ->and(ErrorCode::BAD_REQUEST->statusCode())->toBe(400)
        ->and(ErrorCode::METHOD_NOT_ALLOWED->statusCode())->toBe(405)
        ->and(ErrorCode::REQUEST_TIMEOUT->statusCode())->toBe(408)
        ->and(ErrorCode::PAYLOAD_TOO_LARGE->statusCode())->toBe(413)
        ->and(ErrorCode::UNSUPPORTED_MEDIA_TYPE->statusCode())->toBe(415)
        ->and(ErrorCode::UNPROCESSABLE_ENTITY->statusCode())->toBe(422)
        ->and(ErrorCode::TOO_MANY_REQUESTS->statusCode())->toBe(429)
        ->and(ErrorCode::INTERNAL_ERROR->statusCode())->toBe(500)
        ->and(ErrorCode::NOT_IMPLEMENTED->statusCode())->toBe(501)
        ->and(ErrorCode::BAD_GATEWAY->statusCode())->toBe(502)
        ->and(ErrorCode::SERVICE_UNAVAILABLE->statusCode())->toBe(503)
        ->and(ErrorCode::GATEWAY_TIMEOUT->statusCode())->toBe(504);
});

test('error code returns correct type', function () {
    expect(ErrorCode::VALIDATION_ERROR->type())->toBe('validation')
        ->and(ErrorCode::UNAUTHORIZED->type())->toBe('authentication')
        ->and(ErrorCode::FORBIDDEN->type())->toBe('authorization')
        ->and(ErrorCode::NOT_FOUND->type())->toBe('not_found')
        ->and(ErrorCode::CONFLICT->type())->toBe('conflict')
        ->and(ErrorCode::RATE_LIMITED->type())->toBe('rate_limit')
        ->and(ErrorCode::SERVER_ERROR->type())->toBe('server')
        ->and(ErrorCode::BAD_REQUEST->type())->toBe('client');
});

test('error code returns correct default message', function () {
    expect(ErrorCode::VALIDATION_ERROR->defaultMessage())->toBe('The given data was invalid.')
        ->and(ErrorCode::UNAUTHORIZED->defaultMessage())->toBe('Unauthenticated.')
        ->and(ErrorCode::FORBIDDEN->defaultMessage())->toBe('Forbidden.')
        ->and(ErrorCode::NOT_FOUND->defaultMessage())->toBe('The requested resource was not found.')
        ->and(ErrorCode::CONFLICT->defaultMessage())->toBe('The request conflicts with the current state.')
        ->and(ErrorCode::RATE_LIMITED->defaultMessage())->toBe('Too many requests.')
        ->and(ErrorCode::SERVER_ERROR->defaultMessage())->toBe('Internal server error.')
        ->and(ErrorCode::BAD_REQUEST->defaultMessage())->toBe('Bad request.');
});
