<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Events;

use Illuminate\Foundation\Events\Dispatchable;

class AiPromptLogged
{
    use Dispatchable;

    public function __construct(
        public readonly string $requestId,
        public readonly array $logData,
    ) {}
}
