<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Contracts;

use Illuminate\Database\Eloquent\Builder;
use MageTech\QueryToolkit\Enums\FilterType;

interface FilterInterface
{
    public function getType(): FilterType;

    public function apply(Builder $query, mixed $value, string $property): Builder;

    public function getProperty(): string;

    public function isRelation(): bool;
}
