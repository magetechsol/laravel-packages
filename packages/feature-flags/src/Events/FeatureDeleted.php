<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FeatureDeleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $key,
    ) {}
}
