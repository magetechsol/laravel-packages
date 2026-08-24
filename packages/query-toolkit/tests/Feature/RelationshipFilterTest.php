<?php

declare(strict_types=1);

use MageTech\QueryToolkit\AllowedFilter;
use MageTech\QueryToolkit\QueryBuilder;
use Tests\Fixtures\User;
use Tests\Fixtures\Post;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('filters by relationship', function () {
     = User::create(['name' => 'John', 'email' => 'john@example.com']);
     = User::create(['name' => 'Jane', 'email' => 'jane@example.com']);

    Post::create(['user_id' => ->id, 'title' => 'John Post', 'body' => 'Body', 'is_published' => true]);
    Post::create(['user_id' => ->id, 'title' => 'Jane Post', 'body' => 'Body', 'is_published' => false]);

     = createRequest('GET', '/users', [
        'filter' => ['posts' => ['is_published' => 'true']],
    ]);

     = QueryBuilder::for(User::class, )
        ->allowedFilters([
            AllowedFilter::relationship('posts', 'is_published', \MageTech\QueryToolkit\Filters\BooleanFilter::make('is_published')),
        ])
        ->get();

    expect()->toHaveCount(1)
        ->first()->name->toBe('John');
});

it('filters with numeric relationship', function () {
     = User::create(['name' => 'John', 'email' => 'john@example.com', 'salary' => 50000]);
     = User::create(['name' => 'Jane', 'email' => 'jane@example.com', 'salary' => 100000]);

    Post::create(['user_id' => ->id, 'title' => 'John Post', 'body' => 'Body']);
    Post::create(['user_id' => ->id, 'title' => 'Jane Post', 'body' => 'Body']);

     = createRequest('GET', '/users', [
        'filter' => ['salary' => ['gte' => '75000']],
    ]);

     = QueryBuilder::for(User::class, )
        ->allowedFilters([
            AllowedFilter::gte('salary'),
        ])
        ->get();

    expect()->toHaveCount(1)
        ->first()->name->toBe('Jane');
});
