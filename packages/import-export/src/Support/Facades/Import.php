<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MageTech\ImportExport\Import;

/**
 * @method static Import make(string $modelClass)
 *
 * @see Import
 */
class Import extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Import::class;
    }
}
