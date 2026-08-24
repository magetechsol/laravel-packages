<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MageTech\ImportExport\Export;

/**
 * @method static Export make(string $modelClass)
 *
 * @see Export
 */
class Export extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Export::class;
    }
}
