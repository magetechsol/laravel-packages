<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Enums;

enum AiRequestStatus: string
{
    case Success = 'success';
    case Failed = 'failed';
    case Cached = 'cached';
    case RateLimited = 'rate_limited';
    case QuotaExceeded = 'quota_exceeded';
    case Fallback = 'fallback';
    case Timeout = 'timeout';

    public function label(): string
    {
        return match ($this) {
            self::Success => 'Success',
            self::Failed => 'Failed',
            self::Cached => 'Cached',
            self::RateLimited => 'Rate Limited',
            self::QuotaExceeded => 'Quota Exceeded',
            self::Fallback => 'Fallback',
            self::Timeout => 'Timeout',
        };
    }

    public function isSuccess(): bool
    {
        return $this === self::Success || $this === self::Cached;
    }

    public function isTerminal(): bool
    {
        return ! in_array($this, [self::RateLimited, self::Fallback], true);
    }
}
