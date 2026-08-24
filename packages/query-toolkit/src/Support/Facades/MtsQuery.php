<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MageTech\QueryToolkit\QueryBuilder;

/**
 * @method static \MageTech\QueryToolkit\QueryBuilder for(string $modelClass, ?\Illuminate\Http\Request $request = null)
 * @method static \MageTech\QueryToolkit\QueryBuilder fromQuery(\Illuminate\Database\Eloquent\Builder $query, ?\Illuminate\Http\Request $request = null)
 *
 * @see \MageTech\QueryToolkit\QueryBuilder
 */
class MtsQuery extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'mts-query';
    }
}
