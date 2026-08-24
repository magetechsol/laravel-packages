<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Filters;

use Illuminate\Database\Eloquent\Builder;
use MageTech\QueryToolkit\Enums\FilterType;
use MageTech\QueryToolkit\Exceptions\InvalidFilterQuery;

class NumericFilter extends BaseFilter
{
    protected string $operator;

    public function __construct(string $property, ?string $alias = null, string $operator = '=')
    {
        parent::__construct($property, $alias, FilterType::Numeric);
        $this->operator = $operator;
    }

    public static function make(string $property, ?string $alias = null, string $operator = '='): static
    {
        return new static($property, $alias, $operator);
    }

    public static function greaterThan(string $property, ?string $alias = null): static
    {
        return new static($property, $alias, '>');
    }

    public static function lessThan(string $property, ?string $alias = null): static
    {
        return new static($property, $alias, '<');
    }

    public static function gte(string $property, ?string $alias = null): static
    {
        return new static($property, $alias, '>=');
    }

    public static function lte(string $property, ?string $alias = null): static
    {
        return new static($property, $alias, '<=');
    }

    protected function applyFilter(Builder $query, mixed $value, string $property): Builder
    {
        if (! is_numeric($value)) {
            throw InvalidFilterQuery::invalidFilterValue($this->property, $value, 'Value must be numeric');
        }

        return $query->where($property, $this->operator, $value);
    }
}
