<?php

declare(strict_types=1);

use MageTech\QueryToolkit\QueryBuilder;
use Tests\Fixtures\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('sorts by single field ascending', function () {
    User::create(['name' => 'Charlie', 'email' => 'charlie@example.com']);
    User::create(['name' => 'Alice', 'email' => 'alice@example.com']);
    User::create(['name' => 'Bob', 'email' => 'bob@example.com']);

     = createRequest('GET', '/users', [
        'sort' => 'name',
    ]);

     = QueryBuilder::for(User::class, )
        ->allowedSorts(['name'])
        ->get();

    expect(->pluck('name')->toArray())
        ->toBe(['Alice', 'Bob', 'Charlie']);
});

it('sorts by single field descending', function () {
    User::create(['name' => 'Charlie', 'email' => 'charlie@example.com']);
    User::create(['name' => 'Alice', 'email' => 'alice@example.com']);
    User::create(['name' => 'Bob', 'email' => 'bob@example.com']);

     = createRequest('GET', '/users', [
        'sort' => '-name',
    ]);

     = QueryBuilder::for(User::class, )
        ->allowedSorts(['name'])
        ->get();

    expect(->pluck('name')->toArray())
        ->toBe(['Charlie', 'Bob', 'Alice']);
});

it('sorts by multiple fields', function () {
    User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'created_at' => '2026-01-01']);
    User::create(['name' => 'Bob', 'email' => 'bob@example.com', 'created_at' => '2026-06-01']);
    User::create(['name' => 'Alice', 'email' => 'alice2@example.com', 'created_at' => '2026-03-01']);

     = createRequest('GET', '/users', [
        'sort' => 'name,-created_at',
    ]);

     = QueryBuilder::for(User::class, )
        ->allowedSorts(['name', 'created_at'])
        ->get();

    expect(->pluck('email')->toArray())
        ->toBe(['alice2@example.com', 'alice@example.com', 'bob@example.com']);
});

it('throws exception for disallowed sort', function () {
     = createRequest('GET', '/users', [
        'sort' => 'secret_column',
    ]);

    QueryBuilder::for(User::class, )
        ->allowedSorts(['name'])
        ->get();
})->throws(\MageTech\QueryToolkit\Exceptions\InvalidFilterQuery::class);

it('applies default sort', function () {
    User::create(['name' => 'Charlie', 'email' => 'charlie@example.com']);
    User::create(['name' => 'Alice', 'email' => 'alice@example.com']);
    User::create(['name' => 'Bob', 'email' => 'bob@example.com']);

     = createRequest('GET', '/users', []);

     = QueryBuilder::for(User::class, )
        ->allowedSorts(['name'])
        ->applyDefaultSort('name', 'asc')
        ->get();

    expect(->pluck('name')->toArray())
        ->toBe(['Alice', 'Bob', 'Charlie']);
});
