<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Enums;

enum FilterOperator: string
{
    case Equal = '=';
    case NotEqual = '!=';
    case GreaterThan = '>';
    case LessThan = '<';
    case GreaterThanOrEqual = '>=';
    case LessThanOrEqual = '<=';
    case Like = 'like';
    case NotLike = 'not like';
    case In = 'in';
    case NotIn = 'not in';
    case Between = 'between';
    case NotBetween = 'not between';
    case IsNull = 'is null';
    case IsNotNull = 'is not null';

    public function label(): string
    {
        return match ($this) {
            self::Equal => 'Equals',
            self::NotEqual => 'Does not equal',
            self::GreaterThan => 'Greater than',
            self::LessThan => 'Less than',
            self::GreaterThanOrEqual => 'Greater than or equal',
            self::LessThanOrEqual => 'Less than or equal',
            self::Like => 'Contains',
            self::NotLike => 'Does not contain',
            self::In => 'In list',
            self::NotIn => 'Not in list',
            self::Between => 'Between',
            self::NotBetween => 'Not between',
            self::IsNull => 'Is null',
            self::IsNotNull => 'Is not null',
        };
    }
}
