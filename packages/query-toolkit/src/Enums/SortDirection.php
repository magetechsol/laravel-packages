<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Enums;

enum SortDirection: string
{
    case Ascending = 'asc';
    case Descending = 'desc';

    public function opposite(): self
    {
        return match ($this) {
            self::Ascending => self::Descending,
            self::Descending => self::Ascending,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Ascending => 'Ascending',
            self::Descending => 'Descending',
        };
    }
}
