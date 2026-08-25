<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use MageTech\FeatureFlags\Models\FeatureFlag;

class FeatureEvaluated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public FeatureFlag $flag,
        public mixed $subject,
        public bool $result,
    ) {}
}
