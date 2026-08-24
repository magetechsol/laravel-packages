<?php

declare(strict_types=1);

use MageTech\QueryToolkit\QueryBuilder;
use Tests\Fixtures\User;
use Tests\Fixtures\Post;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('includes relationships', function () {
     = User::create(['name' => 'John', 'email' => 'john@example.com']);
    Post::create(['user_id' => ->id, 'title' => 'First Post', 'body' => 'Body']);
    Post::create(['user_id' => ->id, 'title' => 'Second Post', 'body' => 'Body']);

     = createRequest('GET', '/users', [
        'include' => 'posts',
    ]);

     = QueryBuilder::for(User::class, )
        ->allowedIncludes(['posts'])
        ->get();

    expect(->first()->relations)->toHaveKey('posts')
        ->and(->first()->posts)->toHaveCount(2);
});

it('includes count', function () {
     = User::create(['name' => 'John', 'email' => 'john@example.com']);
    Post::create(['user_id' => ->id, 'title' => 'First Post', 'body' => 'Body']);
    Post::create(['user_id' => ->id, 'title' => 'Second Post', 'body' => 'Body']);

     = createRequest('GET', '/users', [
        'include' => 'posts_count',
    ]);

     = QueryBuilder::for(User::class, )
        ->allowedIncludes([
            \MageTech\QueryToolkit\AllowedInclude::count('posts_count', 'posts'),
        ])
        ->get();

    expect(->first()->posts_count)->toBe(2);
});

it('throws exception for disallowed include', function () {
     = createRequest('GET', '/users', [
        'include' => 'secret_relation',
    ]);

    QueryBuilder::for(User::class, )
        ->allowedIncludes(['posts'])
        ->get();
}()->throws(\MageTech\QueryToolkit\Exceptions\InvalidFilterQuery::class);
