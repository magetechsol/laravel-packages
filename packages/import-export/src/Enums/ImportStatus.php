<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Enums;

enum ImportStatus: string
{
    case Pending = 'pending';
    case Validating = 'validating';
    case Previewing = 'previewing';
    case Mapping = 'mapping';
    case Queued = 'queued';
    case Processing = 'processing';
    case Completing = 'completing';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Validating => 'Validating',
            self::Previewing => 'Previewing',
            self::Mapping => 'Mapping',
            self::Queued => 'Queued',
            self::Processing => 'Processing',
            self::Completing => 'Completing',
            self::Completed => 'Completed',
            self::Failed => 'Failed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [
            self::Pending,
            self::Validating,
            self::Previewing,
            self::Mapping,
            self::Queued,
            self::Processing,
            self::Completing,
        ]);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [
            self::Completed,
            self::Failed,
            self::Cancelled,
        ]);
    }

    public function canCancel(): bool
    {
        return in_array($this, [
            self::Pending,
            self::Queued,
            self::Processing,
        ]);
    }

    public function canRetry(): bool
    {
        return in_array($this, [
            self::Failed,
            self::Cancelled,
        ]);
    }
}
