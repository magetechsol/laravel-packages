<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use MageTech\ApiToolkit\ApiResponse;
use MageTech\ApiToolkit\ApiException;
use Tests\Fixtures\TestController;

uses(RefreshDatabase::class);

beforeEach(function () {
    Route::get('/test/success', [TestController::class, 'success']);
    Route::get('/test/created', [TestController::class, 'created']);
    Route::get('/test/not-found', [TestController::class, 'notFound']);
    Route::get('/test/unauthorized', [TestController::class, 'unauthorized']);
    Route::get('/test/forbidden', [TestController::class, 'forbidden']);
    Route::get('/test/conflict', [TestController::class, 'conflict']);
    Route::get('/test/server-error', [TestController::class, 'serverError']);
});

test('full api flow - success response', function () {
    $response = $this->getJson('/test/success');

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'User retrieved successfully',
            'data' => ['name' => 'John Doe'],
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'data',
            'meta' => [
                'request_id',
                'correlation_id',
                'api_version',
                'timestamp',
            ],
        ]);
});

test('full api flow - created response', function () {
    $response = $this->getJson('/test/created');

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'message' => 'User created successfully',
        ]);
});

test('full api flow - not found response', function () {
    $response = $this->getJson('/test/not-found');

    $response->assertNotFound()
        ->assertJson([
            'success' => false,
            'message' => 'User not found',
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'error' => [
                'code',
                'type',
                'message',
            ],
        ]);
});

test('full api flow - unauthorized response', function () {
    $response = $this->getJson('/test/unauthorized');

    $response->assertUnauthorized()
        ->assertJson([
            'success' => false,
            'error' => [
                'code' => 'UNAUTHORIZED',
                'type' => 'authentication',
            ],
        ]);
});

test('full api flow - forbidden response', function () {
    $response = $this->getJson('/test/forbidden');

    $response->assertForbidden()
        ->assertJson([
            'success' => false,
            'error' => [
                'code' => 'FORBIDDEN',
                'type' => 'authorization',
            ],
        ]);
});

test('full api flow - conflict response', function () {
    $response = $this->getJson('/test/conflict');

    $response->assertStatus(409)
        ->assertJson([
            'success' => false,
            'message' => 'User already exists',
            'error' => [
                'code' => 'CONFLICT',
                'type' => 'conflict',
            ],
        ]);
});

test('full api flow - server error response', function () {
    $response = $this->getJson('/test/server-error');

    $response->assertStatus(500)
        ->assertJson([
            'success' => false,
            'message' => 'Something went wrong',
            'error' => [
                'code' => 'SERVER_ERROR',
                'type' => 'server',
            ],
        ]);
});

test('full api flow - response includes headers', function () {
    $response = $this->getJson('/test/success');

    $response->assertHeader('X-Request-ID')
        ->assertHeader('X-Correlation-ID')
        ->assertHeader('X-API-Version');
});

test('full api flow - custom request id is preserved', function () {
    $response = $this->getJson('/test/success', [
        'X-Request-ID' => 'req_custom123',
    ]);

    $response->assertHeader('X-Request-ID', 'req_custom123');
});

test('full api flow - custom correlation id is preserved', function () {
    $response = $this->getJson('/test/success', [
        'X-Correlation-ID' => 'corr_custom456',
    ]);

    $response->assertHeader('X-Correlation-ID', 'corr_custom456');
});

test('full api flow - api exception is handled', function () {
    Route::get('/test/api-exception', function () {
        throw ApiException::validation([
            'email' => ['The email field is required.'],
        ]);
    });

    $response = $this->getJson('/test/api-exception');

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'type' => 'validation',
            ],
        ]);
});
