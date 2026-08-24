<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use MageTech\ApiToolkit\ApiResponse;

uses(RefreshDatabase::class);

test('paginated response with data', function () {
    $items = [
        ['id' => 1, 'name' => 'John'],
        ['id' => 2, 'name' => 'Jane'],
    ];

    $paginator = new LengthAwarePaginator(
        items: $items,
        total: 50,
        perPage: 15,
        currentPage: 1,
        options: ['path' => '/api/users'],
    );

    $response = ApiResponse::paginated($paginator);

    $json = $response->json();
    expect($json['success'])->toBeTrue()
        ->and($json['data'])->toHaveCount(2)
        ->and($json['meta']['pagination'])->toHaveKeys([
            'current_page',
            'per_page',
            'total',
            'last_page',
            'links',
        ]);
});

test('paginated response pagination metadata', function () {
    $paginator = new LengthAwarePaginator(
        items: [],
        total: 100,
        perPage: 15,
        currentPage: 1,
        options: ['path' => '/api/users'],
    );

    $response = ApiResponse::paginated($paginator);

    $json = $response->json();
    expect($json['meta']['pagination']['current_page'])->toBe(1)
        ->and($json['meta']['pagination']['per_page'])->toBe(15)
        ->and($json['meta']['pagination']['total'])->toBe(100)
        ->and($json['meta']['pagination']['last_page'])->toBe(7);
});

test('paginated response includes links', function () {
    $paginator = new LengthAwarePaginator(
        items: [],
        total: 50,
        perPage: 15,
        currentPage: 2,
        options: ['path' => '/api/users'],
    );

    $response = ApiResponse::paginated($paginator);

    $json = $response->json();
    expect($json['meta']['pagination']['links'])->toHaveKeys(['first', 'last', 'prev', 'next']);
});

test('paginated response with empty items', function () {
    $paginator = new LengthAwarePaginator(
        items: [],
        total: 0,
        perPage: 15,
        currentPage: 1,
        options: ['path' => '/api/users'],
    );

    $response = ApiResponse::paginated($paginator);

    $json = $response->json();
    expect($json['data'])->toBeEmpty()
        ->and($json['meta']['pagination']['total'])->toBe(0);
});

test('paginated response with custom message', function () {
    $paginator = new LengthAwarePaginator(
        items: [],
        total: 0,
        perPage: 15,
        currentPage: 1,
        options: ['path' => '/api/users'],
    );

    $response = ApiResponse::paginated($paginator, 'Users retrieved');

    $json = $response->json();
    expect($json['message'])->toBe('Users retrieved');
});
