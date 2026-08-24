<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MageTech\Webhooks\Outbound\Webhook;

/**
 * @method static \MageTech\Webhooks\Outbound\Webhook send(string $eventName)
 * @method static \MageTech\Webhooks\Models\WebhookDelivery to(string $url)
 * @method static \MageTech\Webhooks\Outbound\Webhook payload(array|object $payload)
 * @method static \MageTech\Webhooks\Outbound\Webhook signWith(string $secret)
 * @method static \MageTech\Webhooks\Outbound\Webhook withHeaders(array $headers)
 * @method static \MageTech\Webhooks\Outbound\Webhook maxAttempts(int $maxAttempts)
 * @method static \MageTech\Webhooks\Models\WebhookDelivery queue(?string $queueName = null)
 * @method static \MageTech\Webhooks\Models\WebhookDelivery now()
 *
 * @see \MageTech\Webhooks\Outbound\Webhook
 */
class Webhook extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Webhook::class;
    }
}
