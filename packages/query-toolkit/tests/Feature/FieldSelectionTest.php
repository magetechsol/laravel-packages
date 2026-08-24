<?php

declare(strict_types=1);

use MageTech\QueryToolkit\QueryBuilder;
use Tests\Fixtures\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('selects specific fields', function () {
    User::create(['name' => 'John', 'email' => 'john@example.com', 'salary' => 50000]);

     = createRequest('GET', '/users', [
        'fields' => ['users' => 'name,email'],
    ]);

     = QueryBuilder::for(User::class, )
        ->get();

    expect(->first()->getAttributes())
        ->toHaveKeys(['name', 'email']);
});

it('validates allowed fields', function () {
    User::create(['name' => 'John', 'email' => 'john@example.com', 'salary' => 50000]);

     = createRequest('GET', '/users', [
        'fields' => ['users' => 'name,salary'],
    ]);

     = QueryBuilder::for(User::class, )
        ->allowedFields(['users' => ['name', 'email']])
        ->get();

    expect(->first()->getAttributes())
        ->toHaveKeys(['name']);
});
