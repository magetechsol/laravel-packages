<?php

declare(strict_types=1);

use MageTech\ApiToolkit\ApiResponse;
use MageTech\ApiToolkit\ApiResponseFactory;
use MageTech\ApiToolkit\DTOs\ErrorData;
use MageTech\ApiToolkit\DTOs\PaginationData;
use MageTech\ApiToolkit\DTOs\ResponseMetadata;

test('success response returns correct structure', function () {
    $response = ApiResponse::success(
        data: ['name' => 'John Doe'],
        message: 'User retrieved successfully',
    );

    expect($response->getStatusCode())->toBe(200);

    $json = $response->json();
    expect($json['success'])->toBeTrue()
        ->and($json['message'])->toBe('User retrieved successfully')
        ->and($json['data'])->toBe(['name' => 'John Doe'])
        ->and($json['meta'])->toHaveKey('request_id')
        ->and($json['meta'])->toHaveKey('timestamp');
});

test('error response returns correct structure', function () {
    $response = ApiResponse::error(
        message: 'Bad request',
        code: 400,
    );

    expect($response->getStatusCode())->toBe(400);

    $json = $response->json();
    expect($json['success'])->toBeFalse()
        ->and($json['message'])->toBe('Bad request')
        ->and($json['error'])->toHaveKey('code')
        ->and($json['error'])->toHaveKey('type')
        ->and($json['error']['code'])->toBe('BAD_REQUEST')
        ->and($json['error']['type'])->toBe('client');
});

test('created response returns 201 status', function () {
    $response = ApiResponse::created(
        data: ['id' => 1],
        message: 'User created',
    );

    expect($response->getStatusCode())->toBe(201);

    $json = $response->json();
    expect($json['success'])->toBeTrue()
        ->and($json['message'])->toBe('User created');
});

test('no content response returns 204 status', function () {
    $response = ApiResponse::noContent();

    expect($response->getStatusCode())->toBe(204);

    $json = $response->json();
    expect($json['success'])->toBeTrue()
        ->and($json['data'])->toBeNull();
});

test('unauthorized response returns 401 status', function () {
    $response = ApiResponse::unauthorized();

    expect($response->getStatusCode())->toBe(401);

    $json = $response->json();
    expect($json['success'])->toBeFalse()
        ->and($json['error']['code'])->toBe('UNAUTHORIZED')
        ->and($json['error']['type'])->toBe('authentication');
});

test('forbidden response returns 403 status', function () {
    $response = ApiResponse::forbidden();

    expect($response->getStatusCode())->toBe(403);

    $json = $response->json();
    expect($json['success'])->toBeFalse()
        ->and($json['error']['code'])->toBe('FORBIDDEN')
        ->and($json['error']['type'])->toBe('authorization');
});

test('not found response returns 404 status', function () {
    $response = ApiResponse::notFound();

    expect($response->getStatusCode())->toBe(404);

    $json = $response->json();
    expect($json['success'])->toBeFalse()
        ->and($json['error']['code'])->toBe('NOT_FOUND')
        ->and($json['error']['type'])->toBe('not_found');
});

test('conflict response returns 409 status', function () {
    $response = ApiResponse::conflict();

    expect($response->getStatusCode())->toBe(409);

    $json = $response->json();
    expect($json['success'])->toBeFalse()
        ->and($json['error']['code'])->toBe('CONFLICT')
        ->and($json['error']['type'])->toBe('conflict');
});

test('throttle response returns 429 status', function () {
    $response = ApiResponse::throttle(retryAfter: 60);

    expect($response->getStatusCode())->toBe(429);

    $json = $response->json();
    expect($json['success'])->toBeFalse()
        ->and($json['error']['code'])->toBe('RATE_LIMITED')
        ->and($json['error']['type'])->toBe('rate_limit');
});

test('server error response returns 500 status', function () {
    $response = ApiResponse::serverError();

    expect($response->getStatusCode())->toBe(500);

    $json = $response->json();
    expect($json['success'])->toBeFalse()
        ->and($json['error']['code'])->toBe('SERVER_ERROR')
        ->and($json['error']['type'])->toBe('server');
});

test('custom response can return any status code', function () {
    $response = ApiResponse::custom(
        code: 418,
        message: "I'm a teapot",
    );

    expect($response->getStatusCode())->toBe(418);

    $json = $response->json();
    expect($json['success'])->toBeFalse()
        ->and($json['message'])->toBe("I'm a teapot");
});

test('response includes request_id when configured', function () {
    config(['mts-api.response.include_request_id' => true]);

    $response = ApiResponse::success();

    $json = $response->json();
    expect($json['meta']['request_id'])->not->toBeNull()
        ->and($json['meta']['request_id'])->toContain('req_');
});

test('response includes timestamp when configured', function () {
    config(['mts-api.response.include_timestamp' => true]);

    $response = ApiResponse::success();

    $json = $response->json();
    expect($json['meta']['timestamp'])->not->toBeNull();
});

test('response includes api_version when configured', function () {
    config(['mts-api.response.include_api_version' => true]);

    $response = ApiResponse::success();

    $json = $response->json();
    expect($json['meta']['api_version'])->not->toBeNull()
        ->and($json['meta']['api_version'])->toBe('v1');
});

test('response excludes meta when envelope is disabled', function () {
    config(['mts-api.response.envelope' => false]);

    $response = ApiResponse::success(
        data: ['name' => 'John'],
        message: 'Success',
    );

    $json = $response->json();
    expect($json)->not->toHaveKey('success')
        ->and($json)->not->toHaveKey('message')
        ->and($json)->toHaveKey('name')
        ->and($json['name'])->toBe('John');
});
