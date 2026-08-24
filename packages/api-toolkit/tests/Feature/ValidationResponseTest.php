<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use MageTech\ApiToolkit\ApiResponse;

uses(RefreshDatabase::class);

test('validation error response with Laravel exception', function () {
    $validator = Validator::make(
        ['email' => 'invalid-email'],
        ['email' => 'required|email'],
    );

    $exception = $validator->errors()->first()
        ? \Illuminate\Validation\ValidationException::withMessages($validator->errors()->toArray())
        : new \Illuminate\Validation\ValidationException($validator);

    $response = ApiResponse::validation($exception);

    expect($response->getStatusCode())->toBe(422);

    $json = $response->json();
    expect($json['success'])->toBeFalse()
        ->and($json['error']['code'])->toBe('VALIDATION_ERROR')
        ->and($json['error']['type'])->toBe('validation')
        ->and($json['error']['details'])->toHaveKey('email');
});

test('validation error response structure', function () {
    $errors = [
        'email' => ['The email field is required.'],
        'password' => ['The password must be at least 8 characters.'],
    ];

    $exception = \Illuminate\Validation\ValidationException::withMessages($errors);
    $response = ApiResponse::validation($exception);

    $json = $response->json();
    expect($json['error']['details'])->toHaveKeys(['email', 'password'])
        ->and($json['error']['details']['email'])->toBe(['The email field is required.']);
});

test('validation error response includes meta', function () {
    $exception = \Illuminate\Validation\ValidationException::withMessages([
        'name' => ['The name field is required.'],
    ]);

    $response = ApiResponse::validation($exception);

    $json = $response->json();
    expect($json['meta'])->toHaveKey('request_id')
        ->and($json['meta'])->toHaveKey('timestamp');
});

test('validation error with multiple errors per field', function () {
    $errors = [
        'email' => [
            'The email field is required.',
            'The email must be a valid email address.',
        ],
    ];

    $exception = \Illuminate\Validation\ValidationException::withMessages($errors);
    $response = ApiResponse::validation($exception);

    $json = $response->json();
    expect($json['error']['details']['email'])->toHaveCount(2);
});
