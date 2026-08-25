<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Events;

use Illuminate\Foundation\Events\Dispatchable;
use MageTech\AIGateway\DTOs\ResolvedModel;

class AiSent
{
    use Dispatchable;

    public function __construct(
        public readonly ResolvedModel $resolved,
        public readonly string $requestId,
        public readonly mixed $response,
    ) {}
}
