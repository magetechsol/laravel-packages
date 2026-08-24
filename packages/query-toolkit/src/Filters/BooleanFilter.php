<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Filters;

use Illuminate\Database\Eloquent\Builder;
use MageTech\QueryToolkit\Enums\FilterType;

class BooleanFilter extends BaseFilter
{
    public function __construct(string $property, ?string $alias = null)
    {
        parent::__construct($property, $alias, FilterType::Boolean);
    }

    public static function make(string $property, ?string $alias = null): static
    {
        return new static($property, $alias);
    }

    protected function applyFilter(Builder $query, mixed $value, string $property): Builder
    {
        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);

        return $query->where($property, '=', $value);
    }
}
