<?php

declare(strict_types=1);

namespace MageTech\Workflow\Enums;

enum StepType: string
{
    case Action = 'action';
    case Approval = 'approval';
    case Condition = 'condition';
    case Parallel = 'parallel';
    case Delay = 'delay';
    case Complete = 'complete';

    public function label(): string
    {
        return match ($this) {
            self::Action => 'Action',
            self::Approval => 'Approval',
            self::Condition => 'Condition',
            self::Parallel => 'Parallel',
            self::Delay => 'Delay',
            self::Complete => 'Complete',
        };
    }

    public function isAsync(): bool
    {
        return in_array($this, [self::Action, self::Parallel, self::Delay]);
    }
}
