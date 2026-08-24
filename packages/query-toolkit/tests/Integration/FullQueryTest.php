<?php

declare(strict_types=1);

use MageTech\QueryToolkit\AllowedFilter;
use MageTech\QueryToolkit\QueryBuilder;
use Tests\Fixtures\User;
use Tests\Fixtures\Post;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('executes a complete query with all features', function () {
    $john = User::create(['name' => 'John Doe', 'email' => 'john@example.com', 'is_active' => true, 'salary' => 80000]);
    $jane = User::create(['name' => 'Jane Smith', 'email' => 'jane@example.com', 'is_active' => true, 'salary' => 95000]);
    $bob = User::create(['name' => 'Bob Wilson', 'email' => 'bob@example.com', 'is_active' => false, 'salary' => 45000]);

    Post::create(['user_id' => $john->id, 'title' => 'John Post 1', 'body' => 'Body', 'is_published' => true]);
    Post::create(['user_id' => $john->id, 'title' => 'John Post 2', 'body' => 'Body', 'is_published' => false]);
    Post::create(['user_id' => $jane->id, 'title' => 'Jane Post', 'body' => 'Body', 'is_published' => true]);

    $request = createRequest('GET', '/users', [
        'filter' => [
            'is_active' => 'true',
            'salary' => ['gte' => '50000'],
        ],
        'sort' => '-salary',
        'include' => 'posts',
        'search' => 'ohn',
        'per_page' => '10',
    ]);

    $result = QueryBuilder::for(User::class, $request)
        ->allowedFilters([
            AllowedFilter::boolean('is_active'),
            AllowedFilter::gte('salary'),
        ])
        ->allowedSorts(['salary', 'name'])
        ->allowedIncludes(['posts'])
        ->searchable(['name', 'email'])
        ->paginate();

    expect($result->paginator)->toHaveCount(2)
        ->and($result->currentPage)->toBe(1);
});

it('handles empty queries gracefully', function () {
    User::create(['name' => 'John', 'email' => 'john@example.com']);

    $request = createRequest('GET', '/users', []);

    $result = QueryBuilder::for(User::class, $request)
        ->allowedFilters(['name'])
        ->get();

    expect($result)->toHaveCount(1);
});

it('works with query builder facade', function () {
    User::create(['name' => 'John', 'email' => 'john@example.com']);
    User::create(['name' => 'Jane', 'email' => 'jane@example.com']);

    $request = createRequest('GET', '/users', [
        'filter' => ['name' => 'John'],
    ]);

    $result = \MageTech\QueryToolkit\Support\Facades\MtsQuery::for(User::class, $request)
        ->allowedFilters(['name'])
        ->get();

    expect($result)->toHaveCount(1)
        ->first()->name->toBe('John');
});
