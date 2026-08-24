<?php

declare(strict_types=1);

test('api_success helper returns success response', function () {
    $response = api_success(data: ['name' => 'John'], message: 'User retrieved');

    expect($response->getStatusCode())->toBe(200);

    $json = $response->json();
    expect($json['success'])->toBeTrue()
        ->and($json['message'])->toBe('User retrieved')
        ->and($json['data']['name'])->toBe('John');
});

test('api_error helper returns error response', function () {
    $response = api_error(message: 'Bad request', code: 400);

    expect($response->getStatusCode())->toBe(400);

    $json = $response->json();
    expect($json['success'])->toBeFalse()
        ->and($json['message'])->toBe('Bad request');
});

test('api_created helper returns created response', function () {
    $response = api_created(data: ['id' => 1]);

    expect($response->getStatusCode())->toBe(201);
});

test('api_no_content helper returns no content response', function () {
    $response = api_no_content();

    expect($response->getStatusCode())->toBe(204);
});

test('api_unauthorized helper returns unauthorized response', function () {
    $response = api_unauthorized();

    expect($response->getStatusCode())->toBe(401);
});

test('api_forbidden helper returns forbidden response', function () {
    $response = api_forbidden();

    expect($response->getStatusCode())->toBe(403);
});

test('api_not_found helper returns not found response', function () {
    $response = api_not_found();

    expect($response->getStatusCode())->toBe(404);
});

test('api_throttle helper returns throttle response', function () {
    $response = api_throttle(retryAfter: 30);

    expect($response->getStatusCode())->toBe(429);
});

test('api_server_error helper returns server error response', function () {
    $response = api_server_error();

    expect($response->getStatusCode())->toBe(500);
});

test('generate_request_id helper generates request ID', function () {
    $requestId = generate_request_id();

    expect($requestId)->toContain('req_')
        ->and(strlen($requestId))->toBeGreaterThan(4);
});

test('generate_correlation_id helper generates correlation ID', function () {
    $correlationId = generate_correlation_id();

    expect($correlationId)->toContain('corr_')
        ->and(strlen($correlationId))->toBeGreaterThan(5);
});
