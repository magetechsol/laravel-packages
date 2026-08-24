<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface SearchInterface
{
    public function apply(Builder $query, string $term): Builder;

    public function getFields(): array;

    public function getWeightedFields(): array;
}
