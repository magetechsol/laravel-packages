<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Enums;

enum SearchDriver: string
{
    case Like = 'like';
    case FullText = 'fulltext';

    public function label(): string
    {
        return match ($this) {
            self::Like => 'LIKE Search',
            self::FullText => 'Full-Text Search',
        };
    }
}
