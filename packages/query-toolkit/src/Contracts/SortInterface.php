<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface SortInterface
{
    public function apply(Builder $query, bool $descending): Builder;

    public function getProperty(): string;

    public function getAlias(): ?string;
}
