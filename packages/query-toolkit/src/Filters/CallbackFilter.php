<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use MageTech\QueryToolkit\Enums\FilterType;

class CallbackFilter extends BaseFilter
{
    protected Closure $callback;

    public function __construct(string $property, Closure $callback, ?string $alias = null)
    {
        parent::__construct($property, $alias, FilterType::Callback);
        $this->callback = $callback;
    }

    public static function make(string $property, Closure $callback, ?string $alias = null): static
    {
        return new static($property, $callback, $alias);
    }

    protected function applyFilter(Builder $query, mixed $value, string $property): Builder
    {
        return ($this->callback)($query, $value, $property);
    }
}
