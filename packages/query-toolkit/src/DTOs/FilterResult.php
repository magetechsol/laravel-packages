<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\DTOs;

use Illuminate\Database\Eloquent\Builder;

readonly class FilterResult
{
    public function __construct(
        public Builder $query,
        public array $appliedFilters,
        public array $skippedFilters,
    ) {}

    public static function empty(Builder $query): static
    {
        return new static($query, [], []);
    }

    public function withAdditionalFilter(string $name, mixed $value): static
    {
        return new static(
            $this->query,
            array_merge($this->appliedFilters, [$name => $value]),
            $this->skippedFilters,
        );
    }

    public function withSkippedFilter(string $name, string $reason): static
    {
        return new static(
            $this->query,
            $this->appliedFilters,
            array_merge($this->skippedFilters, [$name => $reason]),
        );
    }
}
