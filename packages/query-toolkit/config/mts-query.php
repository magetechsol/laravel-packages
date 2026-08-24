<?php

declare(strict_types=1);

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

    'default_filter_type' => 'exact',

    'default_per_page' => 15,
    'max_per_page' => 100,
    'max_pages' => null,

    'delimiter' => ',',

    'strict_mode' => env('MTS_QUERY_STRICT_MODE', true),

    'ignore_invalid_filters' => env('MTS_QUERY_IGNORE_INVALID', false),

    'operators' => [
        'eq',
        'neq',
        'gt',
        'lt',
        'gte',
        'lte',
        'like',
        'not_like',
        'in',
        'not_in',
        'between',
        'not_between',
    ],

    'search' => [
        'enabled' => true,
        'default_driver' => 'like',
        'min_length' => 2,
        'max_length' => 200,
    ],

    'cache' => [
        'enabled' => env('MTS_QUERY_CACHE_ENABLED', true),
        'prefix' => 'mts_query_',
        'ttl' => 3600,
    ],

    'security' => [
        'max_filters_per_request' => 20,
        'max_sorts_per_request' => 5,
        'max_includes_per_request' => 10,
        'prevent_sql_injection' => true,
        'validate_column_existence' => true,
    ],

    'errors' => [
        'throw_on_invalid_filter' => true,
        'throw_on_invalid_sort' => true,
        'throw_on_invalid_include' => true,
        'throw_on_invalid_field' => true,
    ],

];