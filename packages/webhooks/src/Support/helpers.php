<?php

declare(strict_types=1);

use MageTech\Webhooks\Outbound\Webhook;

if (! function_exists('webhook')) {
    function webhook(string $eventName): Webhook
    {
        return Webhook::send($eventName);
    }
}

if (! function_exists('webhook_mask')) {
    function webhook_mask(array $data): array
    {
        return app(\MageTech\Webhooks\Support\SensitiveDataMasker::class)->mask($data);
    }
}

if (! function_exists('webhook_retry_delay')) {
    function webhook_retry_delay(int $attempt, int $baseDelay = 60, int $maxDelay = 3600, float $multiplier = 2.0): int
    {
        return app(\MageTech\Webhooks\Support\RetryStrategy::class)
            ->calculateDelay($attempt, $baseDelay, $maxDelay, $multiplier);
    }
}
