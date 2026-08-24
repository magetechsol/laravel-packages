<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use MageTech\QueryToolkit\Enums\FilterType;
use MageTech\QueryToolkit\Exceptions\InvalidFilterQuery;

class DateRangeFilter extends BaseFilter
{
    public function __construct(string $property, ?string $alias = null)
    {
        parent::__construct($property, $alias, FilterType::DateRange);
    }

    public static function make(string $property, ?string $alias = null): static
    {
        return new static($property, $alias);
    }

    protected function applyFilter(Builder $query, mixed $value, string $property): Builder
    {
        if (! is_array($value)) {
            throw InvalidFilterQuery::invalidFilterValue(
                $this->property,
                $value,
                'Date range requires an array with "from" and/or "to" keys'
            );
        }

        $from = $value['from'] ?? $value['start'] ?? null;
        $to = $value['to'] ?? $value['end'] ?? null;

        if ($from) {
            try {
                $fromDate = Carbon::parse($from);
                $query->whereDate($property, '>=', $fromDate);
            } catch (\Exception $e) {
                throw InvalidFilterQuery::invalidFilterValue($this->property, $from, 'Invalid from date format');
            }
        }

        if ($to) {
            try {
                $toDate = Carbon::parse($to);
                $query->whereDate($property, '<=', $toDate);
            } catch (\Exception $e) {
                throw InvalidFilterQuery::invalidFilterValue($this->property, $to, 'Invalid to date format');
            }
        }

        return $query;
    }
}
