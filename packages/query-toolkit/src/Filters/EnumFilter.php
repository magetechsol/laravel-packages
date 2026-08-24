<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Filters;

use Illuminate\Database\Eloquent\Builder;
use MageTech\QueryToolkit\Enums\FilterType;
use MageTech\QueryToolkit\Exceptions\InvalidFilterQuery;

class EnumFilter extends BaseFilter
{
    protected array $allowedValues;

    public function __construct(string $property, array $allowedValues, ?string $alias = null)
    {
        parent::__construct($property, $alias, FilterType::Enum);
        $this->allowedValues = $allowedValues;
    }

    public static function make(string $property, array $allowedValues, ?string $alias = null): static
    {
        return new static($property, $allowedValues, $alias);
    }

    public function getAllowedValues(): array
    {
        return $this->allowedValues;
    }

    protected function applyFilter(Builder $query, mixed $value, string $property): Builder
    {
        $values = is_array($value) ? $value : [$value];

        foreach ($values as $val) {
            if (! in_array($val, $this->allowedValues, true)) {
                throw InvalidFilterQuery::invalidFilterValue(
                    $this->property,
                    $val,
                    'Allowed values: ' . implode(', ', $this->allowedValues)
                );
            }
        }

        if (is_array($value)) {
            return $query->whereIn($property, $values);
        }

        return $query->where($property, '=', $value);
    }
}
