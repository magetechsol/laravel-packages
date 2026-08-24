<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit;

use Closure;
use MageTech\QueryToolkit\Contracts\SortInterface;

class AllowedSort
{
    public function __construct(
        protected SortInterface $sort,
    ) {}

    public static function key(string $property, ?string $alias = null): static
    {
        return new static(new \MageTech\QueryToolkit\Sorts\DefaultSort($property, $alias));
    }

    public static function custom(string $property, SortInterface $sort): static
    {
        return new static($sort);
    }

    public static function macro(string $property, Closure $callback): static
    {
        return new static(new \MageTech\QueryToolkit\Sorts\MacroSort($property, $callback));
    }

    public function getSort(): SortInterface
    {
        return $this->sort;
    }

    public function getProperty(): string
    {
        return $this->sort->getProperty();
    }

    public function getAlias(): ?string
    {
        return $this->sort->getAlias();
    }
}
