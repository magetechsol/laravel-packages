<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Exceptions;

use InvalidArgumentException;

class UnauthorizedRelation extends InvalidArgumentException
{
    public static function relationNotAllowed(string $relation, array $allowed): static
    {
        return new static(
            "The relationship [{$relation}] is not allowed. Allowed relationships: " . implode(', ', $allowed) . '.'
        );
    }

    public static function nestedRelationNotAllowed(string $parent, string $child, array $allowed): static
    {
        return new static(
            "The nested relationship [{$parent}.{$child}] is not allowed. Allowed nested relationships: " . implode(', ', $allowed) . '.'
        );
    }
}
