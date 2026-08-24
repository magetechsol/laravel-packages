<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use MageTech\QueryToolkit\Enums\FilterType;
use MageTech\QueryToolkit\Exceptions\InvalidFilterQuery;

class DateFilter extends BaseFilter
{
    protected string $operator;

    public function __construct(string $property, ?string $alias = null, string $operator = '=')
    {
        parent::__construct($property, $alias, FilterType::Date);
        $this->operator = $operator;
    }

    public static function make(string $property, ?string $alias = null, string $operator = '='): static
    {
        return new static($property, $alias, $operator);
    }

    public static function from(string $property, ?string $alias = null): static
    {
        return new static($property, $alias, '>=');
    }

    public static function to(string $property, ?string $alias = null): static
    {
        return new static($property, $alias, '<=');
    }

    protected function applyFilter(Builder $query, mixed $value, string $property): Builder
    {
        try {
            $date = Carbon::parse($value);
        } catch (\Exception $e) {
            throw InvalidFilterQuery::invalidFilterValue($this->property, $value, 'Invalid date format');
        }

        return $query->whereDate($property, $this->operator, $date);
    }
}
