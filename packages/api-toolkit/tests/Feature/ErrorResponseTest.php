<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use MageTech\ApiToolkit\ApiResponse;
use MageTech\ApiToolkit\ApiException;

uses(RefreshDatabase::class);

test('error response with 400 status', function () {
    $response = ApiResponse::error('Bad request', 400);

    $json = $response->json();
    expect($json['success'])->toBeFalse()
        ->and($json['message'])->toBe('Bad request')
        ->and($json['error']['code'])->toBe('BAD_REQUEST')
        ->and($json['error']['type'])->toBe('client');
});

test('error response with custom error code', function () {
    $response = ApiResponse::error('Custom error', 418, 'CUSTOM_ERROR');

    $json = $response->json();
    expect($json['error']['code'])->toBe('CUSTOM_ERROR');
});

test('error response with details', function () {
    $response = ApiResponse::error('Bad request', 400, data: ['field' => 'invalid']);

    $json = $response->json();
    expect($json['data'])->toBe(['field' => 'invalid']);
});

test('error response includes meta', function () {
    $response = ApiResponse::error('Error', 400);

    $json = $response->json();
    expect($json['meta'])->toHaveKey('request_id')
        ->and($json['meta'])->toHaveKey('timestamp');
});

test('api exception renders as json response', function () {
    $exception = ApiException::new('Test error', 400, 'TEST_ERROR');
    $response = $exception->render(request());

    expect($response->getStatusCode())->toBe(400);

    $json = $response->json();
    expect($json['success'])->toBeFalse()
        ->and($json['error']['code'])->toBe('TEST_ERROR');
});

test('api exception not found renders correctly', function () {
    $exception = ApiException::notFound('User not found');
    $response = $exception->render(request());

    expect($response->getStatusCode())->toBe(404);

    $json = $response->json();
    expect($json['error']['code'])->toBe('NOT_FOUND')
        ->and($json['error']['type'])->toBe('not_found');
});

test('api exception unauthorized renders correctly', function () {
    $exception = ApiException::unauthorized();
    $response = $exception->render(request());

    expect($response->getStatusCode())->toBe(401);

    $json = $response->json();
    expect($json['error']['code'])->toBe('UNAUTHORIZED')
        ->and($json['error']['type'])->toBe('authentication');
});

test('api exception forbidden renders correctly', function () {
    $exception = ApiException::forbidden();
    $response = $exception->render(request());

    expect($response->getStatusCode())->toBe(403);

    $json = $response->json();
    expect($json['error']['code'])->toBe('FORBIDDEN')
        ->and($json['error']['type'])->toBe('authorization');
});

test('api exception conflict renders correctly', function () {
    $exception = ApiException::conflict('Resource already exists');
    $response = $exception->render(request());

    expect($response->getStatusCode())->toBe(409);

    $json = $response->json();
    expect($json['error']['code'])->toBe('CONFLICT')
        ->and($json['error']['type'])->toBe('conflict');
});

test('api exception throttle renders correctly', function () {
    $exception = ApiException::throttle();
    $response = $exception->render(request());

    expect($response->getStatusCode())->toBe(429);

    $json = $response->json();
    expect($json['error']['code'])->toBe('RATE_LIMITED')
        ->and($json['error']['type'])->toBe('rate_limit');
});

test('api exception server error renders correctly', function () {
    $exception = ApiException::serverError();
    $response = $exception->render(request());

    expect($response->getStatusCode())->toBe(500);

    $json = $response->json();
    expect($json['error']['code'])->toBe('SERVER_ERROR')
        ->and($json['error']['type'])->toBe('server');
});
