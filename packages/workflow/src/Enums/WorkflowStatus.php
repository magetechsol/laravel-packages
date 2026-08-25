<?php

declare(strict_types=1);

namespace MageTech\Workflow\Enums;

enum WorkflowStatus: string
{
    case Draft = 'draft';
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Paused = 'paused';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Running => 'Running',
            self::Completed => 'Completed',
            self::Failed => 'Failed',
            self::Cancelled => 'Cancelled',
            self::Paused => 'Paused',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::Draft, self::Running, self::Paused]);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Failed, self::Cancelled]);
    }

    public function canRetry(): bool
    {
        return in_array($this, [self::Failed]);
    }

    public function canCancel(): bool
    {
        return in_array($this, [self::Draft, self::Running, self::Paused]);
    }
}
