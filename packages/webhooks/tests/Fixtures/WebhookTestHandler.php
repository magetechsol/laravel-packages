<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Tests\Fixtures;

use MageTech\Webhooks\Contracts\WebhookHandlerContract;

class WebhookTestHandler implements WebhookHandlerContract
{
    public static array $handled = [];

    public function handle(array $payload, array $headers, string $event, string $provider): void
    {
        self::$handled[] = [
            'payload' => $payload,
            'headers' => $headers,
            'event' => $event,
            'provider' => $provider,
        ];
    }

    public static function reset(): void
    {
        self::$handled = [];
    }
}
