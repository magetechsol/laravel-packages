<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MageTech\Webhooks\Models\Webhook as WebhookModel;

/**
 * @method static \Illuminate\Database\Eloquent\Builder where(string $column, mixed $operator = null, mixed $value = null)
 * @method static \Illuminate\Database\Eloquent\Builder whereIn(string $column, array $values)
 * @method static \Illuminate\Database\Eloquent\Builder whereProvider(string $provider)
 * @method static \Illuminate\Database\Eloquent\Builder whereEvent(string $event)
 * @method static \Illuminate\Database\Eloquent\Builder whereStatus(\MageTech\Webhooks\Enums\WebhookStatus|string $status)
 * @method static \Illuminate\Database\Eloquent\Builder whereReadyForRetry()
 * @method static \Illuminate\Database\Eloquent\Builder whereRecent(int $days = 7)
 *
 * @see \MageTech\Webhooks\Models\Webhook
 */
class WebhookEvent extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return WebhookModel::class;
    }
}
