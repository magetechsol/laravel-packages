<?php

declare(strict_types=1);

use MageTech\QueryToolkit\QueryBuilder;

if (! function_exists('mts_query')) {
    /**
     * Create a new QueryBuilder instance.
     *
     * @param  string  $modelClass
     * @param  \Illuminate\Http\Request|null  $request
     * @return \MageTech\QueryToolkit\QueryBuilder
     */
    function mts_query(string $modelClass, ?\Illuminate\Http\Request $request = null): QueryBuilder
    {
        return QueryBuilder::for($modelClass, $request);
    }
}

if (! function_exists('mts_filter')) {
    /**
     * Create a new AllowedFilter instance.
     *
     * @param  string  $property
     * @param  string|null  $alias
     * @return \MageTech\QueryToolkit\AllowedFilter
     */
    function mts_filter(string $property, ?string $alias = null): \MageTech\QueryToolkit\AllowedFilter
    {
        return \MageTech\QueryToolkit\AllowedFilter::exact($property, $alias);
    }
}

if (! function_exists('mts_sort')) {
    /**
     * Create a new AllowedSort instance.
     *
     * @param  string  $property
     * @param  string|null  $alias
     * @return \MageTech\QueryToolkit\AllowedSort
     */
    function mts_sort(string $property, ?string $alias = null): \MageTech\QueryToolkit\AllowedSort
    {
        return \MageTech\QueryToolkit\AllowedSort::key($property, $alias);
    }
}
