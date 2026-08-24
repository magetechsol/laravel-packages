<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Enums;

enum DeliveryStatus: string
{
    case Pending = 'pending';
    case Success = 'success';
    case Failed = 'failed';
    case Dead = 'dead';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Success => 'Success',
            self::Failed => 'Failed',
            self::Dead => 'Dead Letter',
        };
    }

    public function isTerminal(): bool
    {
        return $this === self::Success || $this === self::Dead;
    }

    public function canRetry(): bool
    {
        return $this === self::Failed;
    }
}
