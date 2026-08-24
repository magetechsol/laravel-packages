# MTS Laravel Query Toolkit

[![Latest Stable Version](https://poser.pugx.org/magetech/laravel-query-toolkit/v/stable)](https://packagist.org/packages/magetech/laravel-query-toolkit)
[![Total Downloads](https://poser.pugx.org/magetech/laravel-query-toolkit/downloads)](https://packagist.org/packages/magetech/laravel-query-toolkit)
[![License](https://poser.pugx.org/magetech/laravel-query-toolkit/license)](https://packagist.org/packages/magetech/laravel-query-toolkit)
[![PHP Version Require](https://poser.pugx.org/magetech/laravel-query-toolkit/require/php)](https://packagist.org/packages/magetech/laravel-query-toolkit)

Enterprise-grade query builder for Laravel APIs with filtering, sorting, searching, pagination, and relationship support.

## Features

- **Advanced Filtering** - Exact, partial, boolean, numeric, date, enum, scope, callback, relationship, and JSON filters
- **Powerful Search** - LIKE and full-text search across multiple fields
- **Flexible Sorting** - Single and multi-field sorting with direction control
- **Relationship Support** - Filter by related model attributes
- **Pagination** - Standard and cursor pagination with configurable limits
- **Field Selection** - Sparse fieldsets for optimized API responses
- **Security** - Whitelist-only approach prevents SQL injection and unauthorized access
- **Extensible** - Custom filters, sorts, and search drivers

## Requirements

- PHP 8.3+
- Laravel 12.x or 13.x

## Installation

`ash
composer require magetech/laravel-query-toolkit
php artisan mts:query:install
`

## Quick Start

`php
use MageTech\QueryToolkit\Support\Facades\MtsQuery;

// In your controller
public function index()
{
     = MtsQuery::for(User::class)
        ->allowedFilters(['name', 'email', 'status'])
        ->allowedSorts(['name', 'created_at'])
        ->allowedIncludes(['posts', 'roles'])
        ->searchable(['name', 'email'])
        ->paginate();

    return response()->json();
}
`

## API Parameters

### Filtering

`
GET /api/users?filter[name]=John
GET /api/users?filter[status]=active
GET /api/users?filter[salary][gte]=50000
GET /api/users?filter[created_at][from]=2026-01-01&filter[created_at][to]=2026-12-31
`

### Sorting

`
GET /api/users?sort=name
GET /api/users?sort=-created_at
GET /api/users?sort=name,-created_at
`

### Searching

`
GET /api/users?search=john
`

### Includes

`
GET /api/users?include=posts,roles
`

### Field Selection

`
GET /api/users?fields[users]=id,name,email
`

### Pagination

`
GET /api/users?page=2&per_page=25
`

## Filter Types

### Exact Filter

`php
AllowedFilter::exact('name')
AllowedFilter::exact('status', 'status_column') // with alias
`

### Partial Filter

`php
AllowedFilter::partial('name') // LIKE %value%
`

### Boolean Filter

`php
AllowedFilter::boolean('is_active')
`

### Numeric Filters

`php
AllowedFilter::numeric('price')
AllowedFilter::gt('price')      // greater than
AllowedFilter::lt('price')      // less than
AllowedFilter::gte('price')     // greater than or equal
AllowedFilter::lte('price')     // less than or equal
`

### Date Filters

`php
AllowedFilter::date('created_at')
AllowedFilter::date('created_at', null, '>=')
AllowedFilter::dateRange('created_at') // from/to
`

### Enum Filter

`php
AllowedFilter::enum('status', ['active', 'inactive', 'banned'])
`

### Scope Filter

`php
AllowedFilter::scope('active')
`

### Callback Filter

`php
AllowedFilter::callback('custom', function (, , ) {
    return ->where('column', 'value');
})
`

### Relationship Filter

`php
AllowedFilter::relationship('posts', 'is_published', BooleanFilter::make('is_published'))
`

### JSON Filter

`php
AllowedFilter::json('metadata', '$.key')
`

## Sorting

`php
->allowedSorts(['name', 'created_at', 'email'])
`

## Includes

`php
->allowedIncludes(['posts', 'roles', 'comments'])

// Count includes
->allowedIncludes([
    AllowedInclude::count('posts_count', 'posts'),
])
`

## Search

`php
// LIKE search
->searchable(['name', 'email', 'description'])

// Full-text search
->searchable(['name', 'description'], SearchDriver::FullText)
`

## Custom Filters

`ash
php artisan mts:query:make-filter ProductStatusFilter
`

`php
<?php

declare(strict_types=1);

namespace App\QueryFilters;

use Illuminate\Database\Eloquent\Builder;
use MageTech\QueryToolkit\Filters\BaseFilter;
use MageTech\QueryToolkit\Enums\FilterType;

class ProductStatusFilter extends BaseFilter
{
    public function __construct(string , ?string  = null)
    {
        parent::__construct(, , FilterType::Custom);
    }

    protected function applyFilter(Builder , mixed , string ): Builder
    {
        return match () {
            'published' => ->where('published_at', '<=', now()),
            'draft' => ->whereNull('published_at'),
            default => ->where(, ),
        };
    }
}
`

## Configuration

`php
// config/mts-query.php
return [
    'parameters' => [
        'filter' => 'filter',
        'sort' => 'sort',
        'include' => 'include',
        'fields' => 'fields',
        'search' => 'search',
        'per_page' => 'per_page',
        'page' => 'page',
    ],
    'default_per_page' => 15,
    'max_per_page' => 100,
    // ...
];
`

## Security

- All filters must be explicitly whitelisted
- SQL injection is prevented through parameterized queries
- Relationship access is validated against allowed relationships
- Pagination limits prevent excessive data retrieval

## Testing

`ash
composer test
`

## License

MTS License. See [LICENSE](LICENSE) for details.
