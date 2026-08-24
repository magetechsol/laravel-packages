<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Concerns;

use Illuminate\Database\Eloquent\Builder;
use MageTech\QueryToolkit\AllowedFilter;
use MageTech\QueryToolkit\Contracts\FilterInterface;
use MageTech\QueryToolkit\Enums\FilterType;
use MageTech\QueryToolkit\Exceptions\InvalidFilterQuery;
use MageTech\QueryToolkit\Filters\NestedRelationshipFilter;
use MageTech\QueryToolkit\Filters\RelationshipFilter;

trait FiltersQuery
{
    protected array $allowedFilters = [];

    protected array $appliedFilters = [];

    protected array $skippedFilters = [];

    public function allowedFilters(array $filters): static
    {
        foreach ($filters as $key => $value) {
            if (is_int($key) && is_string($value)) {
                $this->allowedFilters[$value] = AllowedFilter::exact($value);
            } elseif ($value instanceof AllowedFilter) {
                $name = $value->getProperty();
                $this->allowedFilters[$name] = $value;
            } elseif (is_string($key) && $value instanceof FilterInterface) {
                $this->allowedFilters[$key] = AllowedFilter::custom($key, $value);
            }
        }

        return $this;
    }

    protected function applyFilters(): static
    {
        $filters = $this->request->getFilters();

        foreach ($filters as $name => $value) {
            if (! $this->isFilterAllowed($name)) {
                if (! $this->options['ignore_invalid_filters']) {
                    throw InvalidFilterQuery::disallowedFilter($name, array_keys($this->allowedFilters));
                }
                $this->skippedFilters[] = 'Filter not allowed';
                continue;
            }

            $allowedFilter = $this->getAllowedFilter($name);
            $this->applyFilter($allowedFilter, $value);
        }

        return $this;
    }

    protected function isFilterAllowed(string $name): bool
    {
        return isset($this->allowedFilters[$name]);
    }

    protected function getAllowedFilter(string $name): AllowedFilter
    {
        return $this->allowedFilters[$name];
    }

    protected function applyFilter(AllowedFilter $allowedFilter, mixed $value): void
    {
        $filter = $allowedFilter->getFilter();
        $property = $allowedFilter->getProperty();

        if ($filter instanceof RelationshipFilter || $filter instanceof NestedRelationshipFilter) {
            $this->ensureRelationsAreAllowed($filter);
        }

        $this->query = $filter->apply($this->query, $value, $property);
        $this->appliedFilters[$property] = $value;
    }

    protected function ensureRelationsAreAllowed(RelationshipFilter|NestedRelationshipFilter $filter): void
    {
        if ($filter instanceof RelationshipFilter) {
            $relation = $filter->getRelation();
            if (isset($this->options['allowed_relations']) && ! in_array($relation, $this->options['allowed_relations'])) {
                throw InvalidFilterQuery::disallowedFilter($relation, $this->options['allowed_relations']);
            }
        }

        if ($filter instanceof NestedRelationshipFilter) {
            $relations = $filter->getRelations();
            foreach ($relations as $relation) {
                if (isset($this->options['allowed_relations']) && ! in_array($relation, $this->options['allowed_relations'])) {
                    throw InvalidFilterQuery::disallowedFilter($relation, $this->options['allowed_relations']);
                }
            }
        }
    }

    public function getAppliedFilters(): array
    {
        return $this->appliedFilters;
    }

    public function getSkippedFilters(): array
    {
        return $this->skippedFilters;
    }
}
