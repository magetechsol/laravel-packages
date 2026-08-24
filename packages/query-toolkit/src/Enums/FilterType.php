<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Enums;

enum FilterType: string
{
    case Exact = 'exact';
    case Partial = 'partial';
    case Scope = 'scope';
    case Callback = 'callback';
    case Custom = 'custom';
    case Relationship = 'relationship';
    case NestedRelationship = 'nested_relationship';
    case JSON = 'json';
    case Boolean = 'boolean';
    case Numeric = 'numeric';
    case Date = 'date';
    case DateRange = 'date_range';
    case Enum = 'enum';
}
