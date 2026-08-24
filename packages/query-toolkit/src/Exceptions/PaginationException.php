<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Exceptions;

use InvalidArgumentException;

class PaginationException extends InvalidArgumentException
{
    public static function pageExceedsMaximum(int $page, int $max): static
    {
        return new static(
            "Page [{$page}] exceeds the maximum allowed page [{$max}]."
        );
    }

    public static function perPageExceedsMaximum(int $perPage, int $max): static
    {
        return new static(
            "Per page [{$perPage}] exceeds the maximum allowed per page [{$max}]."
        );
    }

    public static function invalidPerPage(mixed $perPage): static
    {
        return new static(
            "The per_page value [" . var_export($perPage, true) . "] is not a valid positive integer."
        );
    }
}
