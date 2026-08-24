<?php

declare(strict_types=1);

use MageTech\QueryToolkit\QueryBuilderRequest;

uses();

it('parses filter parameters', function () {
     = createRequest('GET', '/users', [
        'filter' => ['name' => 'John', 'status' => 'active'],
    ]);

     = QueryBuilderRequest::fromRequest();

    expect(->getFilter('name'))->toBe('John')
        ->and(->getFilter('status'))->toBe('active')
        ->and(->getFilters())->toHaveCount(2);
});

it('parses sort parameter', function () {
     = createRequest('GET', '/users', [
        'sort' => '-name,created_at',
    ]);

     = QueryBuilderRequest::fromRequest();

    expect(->getSort())->toBe('-name,created_at');
});

it('parses include parameter', function () {
     = createRequest('GET', '/users', [
        'include' => 'posts,comments',
    ]);

     = QueryBuilderRequest::fromRequest();

    expect(->getIncludes())->toBe(['posts', 'comments']);
});

it('parses fields parameter', function () {
     = createRequest('GET', '/users', [
        'fields' => ['users' => 'name,email'],
    ]);

     = QueryBuilderRequest::fromRequest();

    expect(->getFields('users'))->toBe(['name', 'email']);
});

it('parses search parameter', function () {
     = createRequest('GET', '/users', [
        'search' => 'john',
    ]);

     = QueryBuilderRequest::fromRequest();

    expect(->getSearch())->toBe('john');
});

it('parses pagination parameters', function () {
     = createRequest('GET', '/users', [
        'per_page' => '25',
        'page' => '3',
    ]);

     = QueryBuilderRequest::fromRequest();

    expect(->getPerPage())->toBe(25)
        ->and(->getPage())->toBe(3);
});

it('checks if filter exists', function () {
     = createRequest('GET', '/users', [
        'filter' => ['name' => 'John'],
    ]);

     = QueryBuilderRequest::fromRequest();

    expect(->hasFilter('name'))->toBeTrue()
        ->and(->hasFilter('email'))->toBeFalse();
});
