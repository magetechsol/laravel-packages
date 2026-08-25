<?php

declare(strict_types=1);

namespace MageTech\Workflow\Enums;

enum StepStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';
    case Skipped = 'skipped';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Running => 'Running',
            self::Completed => 'Completed',
            self::Failed => 'Failed',
            self::Skipped => 'Skipped',
            self::Cancelled => 'Cancelled',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::Pending, self::Running]);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Failed, self::Skipped, self::Cancelled]);
    }
}
