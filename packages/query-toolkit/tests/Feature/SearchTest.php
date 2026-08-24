<?php

declare(strict_types=1);

use MageTech\QueryToolkit\Enums\SearchDriver;
use MageTech\QueryToolkit\QueryBuilder;
use Tests\Fixtures\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('searches by single field', function () {
    User::create(['name' => 'John Doe', 'email' => 'john@example.com']);
    User::create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);
    User::create(['name' => 'Johnny Cash', 'email' => 'johnny@example.com']);

     = createRequest('GET', '/users', [
        'search' => 'John',
    ]);

     = QueryBuilder::for(User::class, )
        ->searchable(['name'])
        ->get();

    expect()->toHaveCount(2);
});

it('searches across multiple fields', function () {
    User::create(['name' => 'John Doe', 'email' => 'john@example.com']);
    User::create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);
    User::create(['name' => 'Bob Wilson', 'email' => 'johnny@example.com']);

     = createRequest('GET', '/users', [
        'search' => 'john',
    ]);

     = QueryBuilder::for(User::class, )
        ->searchable(['name', 'email'])
        ->get();

    expect()->toHaveCount(2);
});

it('returns empty for no matches', function () {
    User::create(['name' => 'John Doe', 'email' => 'john@example.com']);
    User::create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

     = createRequest('GET', '/users', [
        'search' => 'nonexistent',
    ]);

     = QueryBuilder::for(User::class, )
        ->searchable(['name', 'email'])
        ->get();

    expect()->toHaveCount(0);
});

it('returns all when no search term', function () {
    User::create(['name' => 'John Doe', 'email' => 'john@example.com']);
    User::create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

     = createRequest('GET', '/users', []);

     = QueryBuilder::for(User::class, )
        ->searchable(['name', 'email'])
        ->get();

    expect()->toHaveCount(2);
});
