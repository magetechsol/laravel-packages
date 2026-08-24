<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit;

use Closure;
use MageTech\QueryToolkit\Contracts\FilterInterface;
use MageTech\QueryToolkit\Enums\FilterType;
use MageTech\QueryToolkit\Filters\{
    BaseFilter,
    BooleanFilter,
    CallbackFilter,
    DateFilter,
    DateRangeFilter,
    EnumFilter,
    ExactFilter,
    JSONFilter,
    NestedRelationshipFilter,
    NumericFilter,
    PartialFilter,
    RelationshipFilter,
    ScopeFilter,
};

class AllowedFilter
{
    public function __construct(
        protected FilterInterface $filter,
        protected ?string $internalName = null,
    ) {}

    public static function exact(string $property, ?string $alias = null): static
    {
        return new static(new ExactFilter($property, $alias));
    }

    public static function partial(string $property, ?string $alias = null): static
    {
        return new static(new PartialFilter($property, $alias));
    }

    public static function boolean(string $property, ?string $alias = null): static
    {
        return new static(new BooleanFilter($property, $alias));
    }

    public static function numeric(string $property, ?string $alias = null, string $operator = '='): static
    {
        return new static(new NumericFilter($property, $alias, $operator));
    }

    public static function gt(string $property, ?string $alias = null): static
    {
        return static::numeric($property, $alias, '>');
    }

    public static function lt(string $property, ?string $alias = null): static
    {
        return static::numeric($property, $alias, '<');
    }

    public static function gte(string $property, ?string $alias = null): static
    {
        return static::numeric($property, $alias, '>=');
    }

    public static function lte(string $property, ?string $alias = null): static
    {
        return static::numeric($property, $alias, '<=');
    }

    public static function date(string $property, ?string $alias = null, string $operator = '='): static
    {
        return new static(new DateFilter($property, $alias, $operator));
    }

    public static function dateRange(string $property, ?string $alias = null): static
    {
        return new static(new DateRangeFilter($property, $alias));
    }

    public static function enum(string $property, array $allowedValues, ?string $alias = null): static
    {
        return new static(new EnumFilter($property, $allowedValues, $alias));
    }

    public static function scope(string $property, ?string $alias = null): static
    {
        return new static(new ScopeFilter($property, $alias));
    }

    public static function callback(string $property, Closure $callback, ?string $alias = null): static
    {
        return new static(new CallbackFilter($property, $callback, $alias));
    }

    public static function relationship(string $relation, string $relationProperty, BaseFilter $filter): static
    {
        return new static(new RelationshipFilter($relation, $relationProperty, $filter));
    }

    public static function nestedRelationship(array $relations, string $finalProperty, BaseFilter $filter): static
    {
        return new static(new NestedRelationshipFilter($relations, $finalProperty, $filter));
    }

    public static function json(string $property, string $jsonPath = '$', string $operator = '=', ?string $alias = null): static
    {
        return new static(new JSONFilter($property, $jsonPath, $operator, $alias));
    }

    public static function custom(string $property, FilterInterface $filter): static
    {
        return new static($filter, $property);
    }

    public function getFilter(): FilterInterface
    {
        return $this->filter;
    }

    public function getInternalName(): ?string
    {
        return $this->internalName;
    }

    public function getType(): FilterType
    {
        return $this->filter->getType();
    }

    public function getProperty(): string
    {
        return $this->filter->getProperty();
    }

    public function isRelation(): bool
    {
        return $this->filter->isRelation();
    }
}
