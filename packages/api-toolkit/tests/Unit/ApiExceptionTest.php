<?php

declare(strict_types=1);

use MageTech\ApiToolkit\ApiException;

test('api exception creates exception with message and status code', function () {
    $exception = ApiException::new('Bad request', 400);

    expect($exception->getMessage())->toBe('Bad request')
        ->and($exception->getStatusCode())->toBe(400);
});

test('api exception creates validation exception', function () {
    $errors = [
        'email' => ['The email field is required.'],
    ];

    $exception = ApiException::validation($errors);

    expect($exception->getMessage())->toBe('Validation failed')
        ->and($exception->getStatusCode())->toBe(422)
        ->and($exception->getErrorCode())->toBe('VALIDATION_ERROR')
        ->and($exception->getData())->toBe(['errors' => $errors]);
});

test('api exception creates unauthorized exception', function () {
    $exception = ApiException::unauthorized();

    expect($exception->getMessage())->toBe('Unauthenticated.')
        ->and($exception->getStatusCode())->toBe(401)
        ->and($exception->getErrorCode())->toBe('UNAUTHORIZED');
});

test('api exception creates forbidden exception', function () {
    $exception = ApiException::forbidden();

    expect($exception->getMessage())->toBe('Forbidden.')
        ->and($exception->getStatusCode())->toBe(403)
        ->and($exception->getErrorCode())->toBe('FORBIDDEN');
});

test('api exception creates not found exception', function () {
    $exception = ApiException::notFound();

    expect($exception->getMessage())->toBe('Not Found.')
        ->and($exception->getStatusCode())->toBe(404)
        ->and($exception->getErrorCode())->toBe('NOT_FOUND');
});

test('api exception creates conflict exception', function () {
    $exception = ApiException::conflict();

    expect($exception->getMessage())->toBe('Conflict.')
        ->and($exception->getStatusCode())->toBe(409)
        ->and($exception->getErrorCode())->toBe('CONFLICT');
});

test('api exception creates throttle exception', function () {
    $exception = ApiException::throttle();

    expect($exception->getMessage())->toBe('Too Many Requests.')
        ->and($exception->getStatusCode())->toBe(429)
        ->and($exception->getErrorCode())->toBe('RATE_LIMITED');
});

test('api exception creates server error exception', function () {
    $exception = ApiException::serverError();

    expect($exception->getMessage())->toBe('Internal Server Error.')
        ->and($exception->getStatusCode())->toBe(500)
        ->and($exception->getErrorCode())->toBe('SERVER_ERROR');
});

test('api exception creates from throwable', function () {
    $previous = new \RuntimeException('Original error');
    $exception = ApiException::fromThrowable($previous, hideStackTraces: false);

    expect($exception->getMessage())->toBe('Original error')
        ->and($exception->getStatusCode())->toBe(500);
});

test('api exception hides stack traces when configured', function () {
    $previous = new \RuntimeException('Original error');
    $exception = ApiException::fromThrowable($previous, hideStackTraces: true);

    expect($exception->getMessage())->toBe('An error occurred while processing your request.');
});

test('api exception renders as json response', function () {
    $exception = ApiException::new('Test error', 400, 'TEST_ERROR');

    $response = $exception->render(request());

    expect($response->getStatusCode())->toBe(400);

    $json = $response->json();
    expect($json['success'])->toBeFalse()
        ->and($json['message'])->toBe('Test error')
        ->and($json['error']['code'])->toBe('TEST_ERROR')
        ->and($json['error']['type'])->toBe('client');
});

test('api exception implements http exception interface', function () {
    $exception = ApiException::new('Test', 400);

    expect($exception)->toBeInstanceOf(\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface::class)
        ->and($exception->getStatusCode())->toBe(400)
        ->and($exception->getHeaders())->toBe([]);
});

test('api exception can have custom headers', function () {
    $exception = ApiException::new('Test', 429, 'RATE_LIMITED', headers: ['Retry-After' => 60]);

    expect($exception->getHeaders())->toBe(['Retry-After' => 60]);
});
