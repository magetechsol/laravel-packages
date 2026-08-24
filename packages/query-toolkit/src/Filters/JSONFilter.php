<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Filters;

use Illuminate\Database\Eloquent\Builder;
use MageTech\QueryToolkit\Enums\FilterType;

class JSONFilter extends BaseFilter
{
    protected string $jsonPath;
    protected string $operator;

    public function __construct(string $property, string $jsonPath = '$', string $operator = '=', ?string $alias = null)
    {
        parent::__construct($property, $alias, FilterType::JSON);
        $this->jsonPath = $jsonPath;
        $this->operator = $operator;
    }

    public static function make(string $property, string $jsonPath = '$', string $operator = '=', ?string $alias = null): static
    {
        return new static($property, $jsonPath, $operator, $alias);
    }

    public function getJsonPath(): string
    {
        return $this->jsonPath;
    }

    protected function applyFilter(Builder $query, mixed $value, string $property): Builder
    {
        $driver = $query->getModel()->getConnection()->getDriverName();

        if ($driver === 'mysql') {
            return $query->where("{$property}->{$this->jsonPath}", $this->operator, $value);
        }

        if ($driver === 'pgsql') {
            return $query->whereRaw("{$property}::jsonb @> ?", [json_encode([$this->jsonPath => $value])]);
        }

        return $query->where($property, 'LIKE', '%' . $value . '%');
    }
}
