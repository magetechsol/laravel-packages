<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Sorts;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use MageTech\QueryToolkit\Contracts\SortInterface;

class MacroSort implements SortInterface
{
    public function __construct(
        protected string $property,
        protected Closure $callback,
        protected ?string $alias = null,
    ) {}

    public function apply(Builder $query, bool $descending): Builder
    {
        return ($this->callback)($query, $descending);
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
