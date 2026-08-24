<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Enums;

enum AttemptStatus: string
{
    case Success = 'success';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Success => 'Success',
            self::Failed => 'Failed',
        };
    }
}
