<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Exceptions;

use InvalidArgumentException;

class InvalidRuleException extends InvalidArgumentException
{
    public function __construct(string $message = 'Invalid feature flag rule.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
