<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Exceptions;

use RuntimeException;

class FeatureFlagNotFoundException extends RuntimeException
{
    public function __construct(string $message = 'Feature flag not found.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
