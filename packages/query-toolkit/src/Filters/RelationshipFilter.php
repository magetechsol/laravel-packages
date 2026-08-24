<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Filters;

use Illuminate\Database\Eloquent\Builder;
use MageTech\QueryToolkit\Enums\FilterType;

class RelationshipFilter extends BaseFilter
{
    protected string $relation;
    protected string $relationProperty;
    protected BaseFilter $filter;

    public function __construct(string $relation, string $relationProperty, BaseFilter $filter)
    {
        parent::__construct("{$relation}.{$relationProperty}", null, FilterType::Relationship);
        $this->relation = $relation;
        $this->relationProperty = $relationProperty;
        $this->filter = $filter;
    }

    public static function make(string $relation, string $relationProperty, BaseFilter $filter): static
    {
        return new static($relation, $relationProperty, $filter);
    }

    public function isRelation(): bool
    {
        return true;
    }

    public function getRelation(): string
    {
        return $this->relation;
    }

    public function getRelationProperty(): string
    {
        return $this->relationProperty;
    }

    public function getFilter(): BaseFilter
    {
        return $this->filter;
    }

    protected function applyFilter(Builder $query, mixed $value, string $property): Builder
    {
        $relation = $this->relation;
        $relationProperty = $this->relationProperty;
        $filter = $this->filter;

        return $query->whereHas($relation, function (Builder $q) use ($value, $relationProperty, $filter) {
            $filter->apply($q, $value, $relationProperty);
        });
    }
}
