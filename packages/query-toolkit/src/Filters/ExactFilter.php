<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Filters;

use Illuminate\Database\Eloquent\Builder;
use MageTech\QueryToolkit\Enums\FilterType;

class ExactFilter extends BaseFilter
{
    public function __construct(string $property, ?string $alias = null)
    {
        parent::__construct($property, $alias, FilterType::Exact);
    }

    public static function make(string $property, ?string $alias = null): static
    {
        return new static($property, $alias);
    }

    protected function applyFilter(Builder $query, mixed $value, string $property): Builder
    {
        if (is_array($value)) {
            return $query->whereIn($property, $value);
        }

        if (is_null($value)) {
            return $query->whereNull($property);
        }

        return $query->where($property, '=', $value);
    }
}
