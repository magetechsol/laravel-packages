<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Sorts;

use Illuminate\Database\Eloquent\Builder;
use MageTech\QueryToolkit\Contracts\SortInterface;

class DefaultSort implements SortInterface
{
    public function __construct(
        protected string $property,
        protected ?string $alias = null,
    ) {}

    public static function make(string $property, ?string $alias = null): static
    {
        return new static($property, $alias);
    }

    public function apply(Builder $query, bool $descending): Builder
    {
        $direction = $descending ? 'desc' : 'asc';
        $column = $this->alias ?? $this->property;

        return $query->orderBy($column, $direction);
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }
}
