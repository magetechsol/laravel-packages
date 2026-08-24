<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Filters;

use Illuminate\Database\Eloquent\Builder;
use MageTech\QueryToolkit\Contracts\FilterInterface;
use MageTech\QueryToolkit\Enums\FilterType;

abstract class BaseFilter implements FilterInterface
{
    public function __construct(
        protected string $property,
        protected ?string $alias = null,
        protected FilterType $type = FilterType::Exact,
    ) {}

    abstract protected function applyFilter(Builder $query, mixed $value, string $property): Builder;

    public function apply(Builder $query, mixed $value, string $property): Builder
    {
        $property = $this->alias ?? $property;

        return $this->applyFilter($query, $value, $property);
    }

    public function getType(): FilterType
    {
        return $this->type;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function isRelation(): bool
    {
        return false;
    }

    protected function wrapColumn(string $column): string
    {
        return $column;
    }
}
