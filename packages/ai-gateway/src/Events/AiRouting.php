<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Events;

use Illuminate\Foundation\Events\Dispatchable;
use MageTech\AIGateway\DTOs\ResolvedModel;

class AiRouting
{
    use Dispatchable;

    public function __construct(
        public readonly ?string $provider,
        public readonly ?string $model,
        public readonly ?int $tenantId,
    ) {}
}
