<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface IncludeInterface
{
    public function apply(Builder $query): Builder;

    public function getName(): string;

    public function getRelationName(): string;
}
