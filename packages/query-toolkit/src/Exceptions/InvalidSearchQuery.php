<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Exceptions;

use InvalidArgumentException;

class InvalidSearchQuery extends InvalidArgumentException
{
    public static function searchNotEnabled(string $model): static
    {
        return new static(
            "Search is not enabled for model [{$model}]. Configure searchable fields first."
        );
    }

    public static function invalidSearchDriver(string $driver): static
    {
        return new static(
            "The search driver [{$driver}] is not supported. Supported drivers: like, fulltext."
        );
    }
}
