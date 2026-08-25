<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Events;

use Illuminate\Foundation\Events\Dispatchable;

class AiRateLimited
{
    use Dispatchable;

    public function __construct(
        public readonly ?int $userId,
        public readonly ?int $tenantId,
        public readonly string $limitType,
        public readonly int $currentCount,
        public readonly int $limit,
    ) {}
}
