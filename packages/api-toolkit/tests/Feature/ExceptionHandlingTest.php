<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use MageTech\ApiToolkit\ApiResponse;
use MageTech\ApiToolkit\ExceptionHandling\ExceptionMapper;

uses(RefreshDatabase::class);

test('exception mapper returns correct status code for validation exception', function () {
    $mapper = new ExceptionMapper();
    $exception = \Illuminate\Validation\ValidationException::withMessages([]);

    expect($mapper->getStatusCode($exception))->toBe(422);
});

test('exception mapper returns correct status code for authentication exception', function () {
    $mapper = new ExceptionMapper();
    $exception = new AuthenticationException();

    expect($mapper->getStatusCode($exception))->toBe(401);
});

test('exception mapper returns correct status code for authorization exception', function () {
    $mapper = new ExceptionMapper();
    $exception = new AuthorizationException();

    expect($mapper->getStatusCode($exception))->toBe(403);
});

test('exception mapper returns correct status code for model not found exception', function () {
    $mapper = new ExceptionMapper();
    $exception = new ModelNotFoundException();

    expect($mapper->getStatusCode($exception))->toBe(404);
});

test('exception mapper returns correct error code for validation exception', function () {
    $mapper = new ExceptionMapper();
    $exception = \Illuminate\Validation\ValidationException::withMessages([]);

    expect($mapper->getErrorCode($exception))->toBe('VALIDATION_ERROR');
});

test('exception mapper returns correct error code for authentication exception', function () {
    $mapper = new ExceptionMapper();
    $exception = new AuthenticationException();

    expect($mapper->getErrorCode($exception))->toBe('UNAUTHORIZED');
});

test('exception mapper returns correct error type for validation exception', function () {
    $mapper = new ExceptionMapper();
    $exception = \Illuminate\Validation\ValidationException::withMessages([]);

    expect($mapper->getErrorType($exception))->toBe('validation');
});

test('exception mapper returns correct error type for authentication exception', function () {
    $mapper = new ExceptionMapper();
    $exception = new AuthenticationException();

    expect($mapper->getErrorType($exception))->toBe('authentication');
});

test('exception mapper can register custom mapping', function () {
    $mapper = new ExceptionMapper();
    $mapper->registerMapping(\InvalidArgumentException::class, 400);

    $exception = new \InvalidArgumentException('Test');
    expect($mapper->getStatusCode($exception))->toBe(400);
});

test('exception mapper should log server errors', function () {
    $mapper = new ExceptionMapper();
    $exception = new \RuntimeException('Test');

    expect($mapper->shouldLog($exception))->toBeTrue();
});

test('exception mapper should not log client errors', function () {
    $mapper = new ExceptionMapper();
    $exception = new AuthenticationException();

    expect($mapper->shouldLog($exception))->toBeFalse();
});

test('exception mapper should hide stack traces', function () {
    config(['mts-api.exception_handling.hide_stack_traces' => true]);

    $mapper = new ExceptionMapper();
    expect($mapper->shouldHideStackTraces())->toBeTrue();
});

test('exception mapper is enabled by default', function () {
    config(['mts-api.exception_handling.enabled' => true]);

    $mapper = new ExceptionMapper();
    expect($mapper->isEnabled())->toBeTrue();
});
