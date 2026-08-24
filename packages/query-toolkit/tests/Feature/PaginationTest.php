<?php

declare(strict_types=1);

use MageTech\QueryToolkit\QueryBuilder;
use Tests\Fixtures\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('paginates results', function () {
    User::factory()->count(50)->create();

     = createRequest('GET', '/users', [
        'per_page' => 10,
        'page' => 1,
    ]);

     = QueryBuilder::for(User::class, )
        ->allowedSorts(['id'])
        ->paginate();

    expect(->paginator)->toHaveCount(10)
        ->and(->currentPage)->toBe(1)
        ->and(->perPage)->toBe(10)
        ->and(->total)->toBe(50);
});

it('paginates with custom per page', function () {
    User::factory()->count(25)->create();

     = createRequest('GET', '/users', [
        'per_page' => 5,
    ]);

     = QueryBuilder::for(User::class, )
        ->paginate();

    expect(->paginator)->toHaveCount(5)
        ->and(->perPage)->toBe(5);
});

it('uses default per page from config', function () {
    User::factory()->count(30)->create();

     = createRequest('GET', '/users', []);

    config(['mts-query.default_per_page' => 15]);

     = QueryBuilder::for(User::class, )
        ->paginate();

    expect(->perPage)->toBe(15);
});

it('throws exception when per page exceeds max', function () {
    User::factory()->count(50)->create();

     = createRequest('GET', '/users', [
        'per_page' => 200,
    ]);

    config(['mts-query.max_per_page' => 100]);

    QueryBuilder::for(User::class, )
        ->paginate();
})->throws(\MageTech\QueryToolkit\Exceptions\PaginationException::class);

it('returns correct pagination metadata', function () {
    User::factory()->count(25)->create();

     = createRequest('GET', '/users', [
        'per_page' => 10,
        'page' => 2,
    ]);

     = QueryBuilder::for(User::class, )
        ->paginate();

    expect(->currentPage)->toBe(2)
        ->and(->perPage)->toBe(10)
        ->and(->total)->toBe(25)
        ->and(->lastPage)->toBe(3);
});
