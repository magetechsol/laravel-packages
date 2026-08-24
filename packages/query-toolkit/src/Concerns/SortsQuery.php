<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Concerns;

use Illuminate\Database\Eloquent\Builder;
use MageTech\QueryToolkit\AllowedSort;
use MageTech\QueryToolkit\Contracts\SortInterface;
use MageTech\QueryToolkit\Exceptions\InvalidFilterQuery;

trait SortsQuery
{
    protected array $allowedSorts = [];

    protected array $appliedSorts = [];

    public function allowedSorts(array $sorts): static
    {
        foreach ($sorts as $key => $value) {
            if (is_int($key) && is_string($value)) {
                $this->allowedSorts[$value] = AllowedSort::key($value);
            } elseif ($value instanceof AllowedSort) {
                $this->allowedSorts[$value->getProperty()] = $value;
            } elseif (is_string($key) && $value instanceof SortInterface) {
                $this->allowedSorts[$key] = AllowedSort::custom($key, $value);
            }
        }

        return $this;
    }

    protected function applySorts(): static
    {
        $sort = $this->request->getSort();

        if (is_null($sort)) {
            return $this;
        }

        $sorts = $this->parseSortString($sort);

        foreach ($sorts as $sortField) {
            $descending = str_starts_with($sortField, '-');
            $field = ltrim($sortField, '-');

            if (! $this->isSortAllowed($field)) {
                if (! $this->options['ignore_invalid_filters']) {
                    throw InvalidFilterQuery::disallowedSort($field, array_keys($this->allowedSorts));
                }
                $this->skippedFilters[] = 'Sort not allowed';
                continue;
            }

            $allowedSort = $this->getAllowedSort($field);
            $this->applySort($allowedSort, $descending);
        }

        return $this;
    }

    protected function parseSortString(string $sort): array
    {
        return array_map('trim', explode(',', $sort));
    }

    protected function isSortAllowed(string $field): bool
    {
        return isset($this->allowedSorts[$field]);
    }

    protected function getAllowedSort(string $field): AllowedSort
    {
        return $this->allowedSorts[$field];
    }

    protected function applySort(AllowedSort $allowedSort, bool $descending): void
    {
        $sort = $allowedSort->getSort();
        $this->query = $sort->apply($this->query, $descending);
        $this->appliedSorts[$allowedSort->getProperty()] = $descending ? 'desc' : 'asc';
    }

    public function applyDefaultSort(string $column, string $direction = 'asc'): static
    {
        $this->query->orderBy($column, $direction);

        return $this;
    }

    public function getAppliedSorts(): array
    {
        return $this->appliedSorts;
    }
}
