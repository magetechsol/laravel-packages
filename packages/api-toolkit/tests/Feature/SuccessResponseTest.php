<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use MageTech\ApiToolkit\ApiResponse;
use Tests\Fixtures\UserResource;

uses(RefreshDatabase::class);

test('success response with array data', function () {
    $response = ApiResponse::success(
        data: ['id' => 1, 'name' => 'John Doe'],
        message: 'User retrieved',
    );

    $json = $response->json();
    expect($json['success'])->toBeTrue()
        ->and($json['data'])->toBe(['id' => 1, 'name' => 'John Doe'])
        ->and($json['meta'])->toHaveKey('request_id');
});

test('success response with collection data', function () {
    $users = [
        ['id' => 1, 'name' => 'John'],
        ['id' => 2, 'name' => 'Jane'],
    ];

    $response = ApiResponse::collection($users, 'Users retrieved');

    $json = $response->json();
    expect($json['success'])->toBeTrue()
        ->and($json['data'])->toHaveCount(2)
        ->and($json['message'])->toBe('Users retrieved');
});

test('success response with resource data', function () {
    $user = new \stdClass();
    $user->id = 1;
    $user->name = 'John Doe';
    $user->email = 'john@example.com';
    $user->created_at = now();
    $user->updated_at = now();

    $resource = new UserResource($user);
    $response = ApiResponse::resource($resource, 'User retrieved');

    $json = $response->json();
    expect($json['success'])->toBeTrue()
        ->and($json['data'])->toHaveKey('name')
        ->and($json['data']['name'])->toBe('John Doe');
});

test('success response with null data', function () {
    $response = ApiResponse::success(data: null);

    $json = $response->json();
    expect($json['success'])->toBeTrue()
        ->and($json['data'])->toBeNull();
});

test('success response includes all meta fields', function () {
    $response = ApiResponse::success();

    $json = $response->json();
    expect($json['meta'])->toHaveKeys(['request_id', 'correlation_id', 'api_version', 'timestamp']);
});

test('success response with custom status code', function () {
    $response = ApiResponse::success(data: null, code: 202);

    expect($response->getStatusCode())->toBe(202);
});
