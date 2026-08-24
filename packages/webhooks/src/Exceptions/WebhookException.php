<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Exceptions;

use RuntimeException;

class WebhookException extends RuntimeException
{
    public static function providerNotSupported(string $provider): static
    {
        return new static('Webhook provider not supported: ' . $provider);
    }

    public static function processingFailed(string $reason): static
    {
        return new static('Webhook processing failed: ' . $reason);
    }
}
