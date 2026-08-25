<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Events;

use Illuminate\Foundation\Events\Dispatchable;

class AiFallbackTriggered
{
    use Dispatchable;

    public function __construct(
        public readonly string $fromProvider,
        public readonly ?string $toProvider,
        public readonly string $error,
        public readonly string $requestId,
    ) {}
}
