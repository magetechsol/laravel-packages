<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Events;

use Illuminate\Foundation\Events\Dispatchable;

class AiQuotaExceeded
{
    use Dispatchable;

    public function __construct(
        public readonly ?int $userId,
        public readonly ?int $tenantId,
        public readonly string $quotaType,
        public readonly float $currentUsage,
        public readonly float $limit,
    ) {}
}
