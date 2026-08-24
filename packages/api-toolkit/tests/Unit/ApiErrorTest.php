<?php

declare(strict_types=1);

use MageTech\ApiToolkit\ApiError;

test('api error creates error with message and status code', function () {
    $error = ApiError::new('Bad request', 400);

    expect($error->getMessage())->toBe('Bad request')
        ->and($error->getStatusCode())->toBe(400);
});

test('api error creates validation error', function () {
    $errors = [
        'email' => ['The email field is required.'],
        'password' => ['The password must be at least 8 characters.'],
    ];

    $error = ApiError::validation($errors);

    expect($error->getMessage())->toBe('Validation failed')
        ->and($error->getStatusCode())->toBe(422)
        ->and($error->getErrorCode())->toBe('VALIDATION_ERROR')
        ->and($error->getDetails())->toBe($errors);
});

test('api error creates unauthorized error', function () {
    $error = ApiError::unauthorized();

    expect($error->getMessage())->toBe('Unauthorized')
        ->and($error->getStatusCode())->toBe(401)
        ->and($error->getErrorCode())->toBe('UNAUTHORIZED');
});

test('api error creates forbidden error', function () {
    $error = ApiError::forbidden();

    expect($error->getMessage())->toBe('Forbidden')
        ->and($error->getStatusCode())->toBe(403)
        ->and($error->getErrorCode())->toBe('FORBIDDEN');
});

test('api error creates not found error', function () {
    $error = ApiError::notFound();

    expect($error->getMessage())->toBe('Not Found')
        ->and($error->getStatusCode())->toBe(404)
        ->and($error->getErrorCode())->toBe('NOT_FOUND');
});

test('api error creates conflict error', function () {
    $error = ApiError::conflict();

    expect($error->getMessage())->toBe('Conflict')
        ->and($error->getStatusCode())->toBe(409)
        ->and($error->getErrorCode())->toBe('CONFLICT');
});

test('api error creates rate limited error', function () {
    $error = ApiError::rateLimited();

    expect($error->getMessage())->toBe('Too Many Requests')
        ->and($error->getStatusCode())->toBe(429)
        ->and($error->getErrorCode())->toBe('RATE_LIMITED');
});

test('api error creates server error', function () {
    $error = ApiError::serverError();

    expect($error->getMessage())->toBe('Internal Server Error')
        ->and($error->getStatusCode())->toBe(500)
        ->and($error->getErrorCode())->toBe('SERVER_ERROR');
});

test('api error converts to array', function () {
    $error = ApiError::new('Test error', 400, 'TEST_ERROR');

    $array = $error->toArray();

    expect($array)->toHaveKeys(['code', 'type', 'message'])
        ->and($array['code'])->toBe('TEST_ERROR')
        ->and($array['type'])->toBe('client')
        ->and($array['message'])->toBe('Test error');
});

test('api error converts to json', function () {
    $error = ApiError::new('Test error', 400, 'TEST_ERROR');

    $json = $error->toJson();

    expect(json_decode($json, true))->toHaveKeys(['code', 'type', 'message']);
});

test('api error implements json serializable', function () {
    $error = ApiError::new('Test error', 400, 'TEST_ERROR');

    $serialized = json_encode($error);

    expect(json_decode($serialized, true))->toHaveKeys(['code', 'type', 'message']);
});

test('api error implements responsable', function () {
    $error = ApiError::new('Test error', 400, 'TEST_ERROR');

    $response = $error->toResponse(request());

    expect($response->getStatusCode())->toBe(400)
        ->and($response->json())->toHaveKeys(['code', 'type', 'message']);
});
