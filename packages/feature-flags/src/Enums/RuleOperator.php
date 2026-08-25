<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Enums;

enum RuleOperator: string
{
    case Equals = 'equals';
    case NotEquals = 'not_equals';
    case Contains = 'contains';
    case NotContains = 'not_contains';
    case Starts = 'starts_with';
    case Ends = 'ends_with';
    case GreaterThan = 'greater_than';
    case LessThan = 'less_than';
    case In = 'in';
    case NotIn = 'not_in';
    case Regex = 'regex';

    public function label(): string
    {
        return match ($this) {
            self::Equals => 'Equals',
            self::NotEquals => 'Not Equals',
            self::Contains => 'Contains',
            self::NotContains => 'Does Not Contain',
            self::Starts => 'Starts With',
            self::Ends => 'Ends With',
            self::GreaterThan => 'Greater Than',
            self::LessThan => 'Less Than',
            self::In => 'Is In',
            self::NotIn => 'Is Not In',
            self::Regex => 'Matches Regex',
        };
    }
}
