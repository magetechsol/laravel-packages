<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Exceptions;

use InvalidArgumentException;

class InvalidFilterQuery extends InvalidArgumentException
{
    public static function disallowedFilter(string $filter, array $allowed): static
    {
        return new static(
            "The filter [{$filter}] is not allowed. Allowed filters: " . implode(', ', $allowed) . '.'
        );
    }

    public static function disallowedSort(string $sort, array $allowed): static
    {
        return new static(
            "The sort field [{$sort}] is not allowed. Allowed sorts: " . implode(', ', $allowed) . '.'
        );
    }

    public static function disallowedInclude(string $include, array $allowed): static
    {
        return new static(
            "The include [{$include}] is not allowed. Allowed includes: " . implode(', ', $allowed) . '.'
        );
    }

    public static function disallowedField(string $field, string $resource, array $allowed): static
    {
        return new static(
            "The field [{$field}] is not allowed for resource [{$resource}]. Allowed fields: " . implode(', ', $allowed) . '.'
        );
    }

    public static function invalidFilterValue(string $filter, mixed $value, string $message): static
    {
        return new static(
            "The filter [{$filter}] has an invalid value [{$value}]: {$message}."
        );
    }

    public static function invalidSortDirection(string $sort): static
    {
        return new static(
            "The sort [{$sort}] has an invalid direction. Use '-' prefix for descending sort."
        );
    }

    public static function missingFilterValue(string $filter): static
    {
        return new static(
            "The filter [{$filter}] requires a value but none was provided."
        );
    }
}
