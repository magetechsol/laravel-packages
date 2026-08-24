<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Enums;

enum WebhookStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Processed = 'processed';
    case Failed = 'failed';
    case Dead = 'dead';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Processing => 'Processing',
            self::Processed => 'Processed',
            self::Failed => 'Failed',
            self::Dead => 'Dead Letter',
        };
    }

    public function isActive(): bool
    {
        return $this === self::Pending || $this === self::Processing;
    }

    public function isTerminal(): bool
    {
        return $this === self::Processed || $this === self::Dead;
    }

    public function canRetry(): bool
    {
        return $this === self::Failed;
    }
}
