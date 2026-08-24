<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Outbound;

use Illuminate\Support\Facades\Queue;
use MageTech\Webhooks\Jobs\DeliverOutboundWebhookJob;
use MageTech\Webhooks\Models\WebhookDelivery;

class Dispatcher
{
    public function dispatch(WebhookDelivery $delivery): void
    {
        $job = new DeliverOutboundWebhookJob($delivery);

        $job->onQueue(config('mts-webhooks.outbound.queue', 'default'));

        $connection = config('mts-webhooks.outbound.connection');

        if ($connection !== null) {
            $job->onConnection($connection);
        }

        Queue::push($job);
    }
}
