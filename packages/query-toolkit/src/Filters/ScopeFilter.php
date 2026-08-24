<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Filters;

use Illuminate\Database\Eloquent\Builder;
use MageTech\QueryToolkit\Enums\FilterType;

class ScopeFilter extends BaseFilter
{
    public function __construct(string $property, ?string $alias = null)
    {
        parent::__construct($property, $alias, FilterType::Scope);
    }

    public static function make(string $property, ?string $alias = null): static
    {
        return new static($property, $alias);
    }

    protected function applyFilter(Builder $query, mixed $value, string $property): Builder
    {
        $method = $this->alias ?? $property;

        if (method_exists($query->getModel(), $method)) {
            return $query->{$method}($value);
        }

        return $query->where($property, '=', $value);
    }
}
