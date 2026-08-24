<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Filters;

use Illuminate\Database\Eloquent\Builder;
use MageTech\QueryToolkit\Enums\FilterType;

class NestedRelationshipFilter extends BaseFilter
{
    protected array $relations;
    protected string $finalProperty;
    protected BaseFilter $filter;

    public function __construct(array $relations, string $finalProperty, BaseFilter $filter)
    {
        $property = implode('.', $relations) . '.' . $finalProperty;
        parent::__construct($property, null, FilterType::NestedRelationship);
        $this->relations = $relations;
        $this->finalProperty = $finalProperty;
        $this->filter = $filter;
    }

    public static function make(array $relations, string $finalProperty, BaseFilter $filter): static
    {
        return new static($relations, $finalProperty, $filter);
    }

    public function isRelation(): bool
    {
        return true;
    }

    public function getRelations(): array
    {
        return $this->relations;
    }

    public function getFinalProperty(): string
    {
        return $this->finalProperty;
    }

    public function getFilter(): BaseFilter
    {
        return $this->filter;
    }

    protected function applyFilter(Builder $query, mixed $value, string $property): Builder
    {
        $relations = $this->relations;
        $finalProperty = $this->finalProperty;
        $filter = $this->filter;

        $query->whereHas($relations[0], function (Builder $q) use ($value, $relations, $finalProperty, $filter) {
            $this->applyNestedRelation($q, array_slice($relations, 1), $finalProperty, $filter, $value);
        });

        return $query;
    }

    private function applyNestedRelation(Builder $query, array $remainingRelations, string $finalProperty, BaseFilter $filter, mixed $value): void
    {
        if (empty($remainingRelations)) {
            $filter->apply($query, $value, $finalProperty);

            return;
        }

        $relation = $remainingRelations[0];
        $query->whereHas($relation, function (Builder $q) use ($value, $remainingRelations, $finalProperty, $filter) {
            $this->applyNestedRelation($q, array_slice($remainingRelations, 1), $finalProperty, $filter, $value);
        });
    }
}
