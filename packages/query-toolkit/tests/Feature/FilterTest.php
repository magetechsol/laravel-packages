<?php

declare(strict_types=1);

use MageTech\QueryToolkit\AllowedFilter;
use MageTech\QueryToolkit\Exceptions\InvalidFilterQuery;
use MageTech\QueryToolkit\QueryBuilder;
use Tests\Fixtures\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('applies exact filter', function () {
    User::create(['name' => 'John Doe', 'email' => 'john@example.com']);
    User::create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

     = createRequest('GET', '/users', [
        'filter' => ['name' => 'John Doe'],
    ]);

     = QueryBuilder::for(User::class, )
        ->allowedFilters(['name'])
        ->get();

    expect()->toHaveCount(1)
        ->first()->name->toBe('John Doe');
});

it('applies partial filter', function () {
    User::create(['name' => 'John Doe', 'email' => 'john@example.com']);
    User::create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);
    User::create(['name' => 'Johnny Cash', 'email' => 'johnny@example.com']);

     = createRequest('GET', '/users', [
        'filter' => ['name' => 'John'],
    ]);

     = QueryBuilder::for(User::class, )
        ->allowedFilters([
            AllowedFilter::partial('name'),
        ])
        ->get();

    expect()->toHaveCount(2);
});

it('applies boolean filter', function () {
    User::create(['name' => 'Active User', 'email' => 'active@example.com', 'is_active' => true]);
    User::create(['name' => 'Inactive User', 'email' => 'inactive@example.com', 'is_active' => false]);

     = createRequest('GET', '/users', [
        'filter' => ['is_active' => 'true'],
    ]);

     = QueryBuilder::for(User::class, )
        ->allowedFilters([
            AllowedFilter::boolean('is_active'),
        ])
        ->get();

    expect()->toHaveCount(1)
        ->first()->name->toBe('Active User');
});

it('applies numeric filter with greater than', function () {
    User::create(['name' => 'Low Salary', 'email' => 'low@example.com', 'salary' => 30000]);
    User::create(['name' => 'High Salary', 'email' => 'high@example.com', 'salary' => 80000]);

     = createRequest('GET', '/users', [
        'filter' => ['salary' => ['gte' => 50000]],
    ]);

     = QueryBuilder::for(User::class, )
        ->allowedFilters([
            AllowedFilter::gte('salary'),
        ])
        ->get();

    expect()->toHaveCount(1)
        ->first()->name->toBe('High Salary');
});

it('applies enum filter', function () {
    User::create(['name' => 'Active User', 'email' => 'a@example.com', 'status' => 'active']);
    User::create(['name' => 'Banned User', 'email' => 'b@example.com', 'status' => 'banned']);

     = createRequest('GET', '/users', [
        'filter' => ['status' => 'active'],
    ]);

     = QueryBuilder::for(User::class, )
        ->allowedFilters([
            AllowedFilter::enum('status', ['active', 'inactive', 'banned']),
        ])
        ->get();

    expect()->toHaveCount(1)
        ->first()->status->toBe('active');
});

it('throws exception for disallowed filter', function () {
    User::create(['name' => 'John', 'email' => 'john@example.com']);

     = createRequest('GET', '/users', [
        'filter' => ['secret_column' => 'value'],
    ]);

    QueryBuilder::for(User::class, )
        ->allowedFilters(['name'])
        ->get();
})->throws(InvalidFilterQuery::class);

it('applies callback filter', function () {
    User::create(['name' => 'John', 'email' => 'john@example.com', 'salary' => 50000]);
    User::create(['name' => 'Jane', 'email' => 'jane@example.com', 'salary' => 100000]);

     = createRequest('GET', '/users', [
        'filter' => ['high_earner' => true],
    ]);

     = QueryBuilder::for(User::class, )
        ->allowedFilters([
            AllowedFilter::callback('high_earner', function (, , ) {
                if () {
                    return ->where('salary', '>', 75000);
                }
                return ->where('salary', '<=', 75000);
            }),
        ])
        ->get();

    expect()->toHaveCount(1)
        ->first()->name->toBe('Jane');
});

it('applies date range filter', function () {
    User::create(['name' => 'Old User', 'email' => 'old@example.com', 'created_at' => '2025-01-15']);
    User::create(['name' => 'New User', 'email' => 'new@example.com', 'created_at' => '2026-06-15']);

     = createRequest('GET', '/users', [
        'filter' => ['created_at' => ['from' => '2026-01-01', 'to' => '2026-12-31']],
    ]);

     = QueryBuilder::for(User::class, )
        ->allowedFilters([
            AllowedFilter::dateRange('created_at'),
        ])
        ->get();

    expect()->toHaveCount(1)
        ->first()->name->toBe('New User');
});

it('allows unauthenticated filters when configured', function () {
    User::create(['name' => 'John', 'email' => 'john@example.com']);

     = createRequest('GET', '/users', [
        'filter' => ['unknown_column' => 'value'],
    ]);

     = QueryBuilder::for(User::class, )
        ->allowedFilters(['name'])
        ->allowUnauthenticatedFilters()
        ->get();

    expect()->toHaveCount(1);
});
