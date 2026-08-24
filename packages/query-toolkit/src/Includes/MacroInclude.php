<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Includes;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use MageTech\QueryToolkit\Contracts\IncludeInterface;

class MacroInclude implements IncludeInterface
{
    public function __construct(
        protected string $name,
        protected Closure $callback,
        protected ?string $relationName = null,
    ) {}

    public function apply(Builder $query): Builder
    {
        return ($this->callback)($query, $this->name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRelationName(): string
    {
        return $this->relationName ?? $this->name;
    }
}
