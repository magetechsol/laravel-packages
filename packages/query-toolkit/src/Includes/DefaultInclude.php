<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Includes;

use Illuminate\Database\Eloquent\Builder;
use MageTech\QueryToolkit\Contracts\IncludeInterface;

class DefaultInclude implements IncludeInterface
{
    public function __construct(
        protected string $name,
        protected ?string $relationName = null,
    ) {}

    public static function make(string $name, ?string $relationName = null): static
    {
        return new static($name, $relationName);
    }

    public function apply(Builder $query): Builder
    {
        return $query->with($this->getRelationName());
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
