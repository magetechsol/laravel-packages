<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use MageTech\Webhooks\Models\WebhookDelivery;
use MageTech\Webhooks\Outbound\DeliveryTracker;

class RetryOutboundWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout;

    public int $tries;

    public function __construct(public WebhookDelivery $delivery)
    {
        $this->timeout = config('mts-webhooks.outbound.timeout', 30);
        $this->tries = 1;
        $this->queue = config('mts-webhooks.outbound.queue', 'default');
        $this->connection = config('mts-webhooks.outbound.connection');
    }

    public function handle(): void
    {
        $this->delivery->refresh();

        if (! $this->delivery->canRetry()) {
            return;
        }

        app(DeliveryTracker::class)->deliver($this->delivery);
    }

    public function failed(\Throwable $exception): void
    {
        if (config('mts-webhooks.logging.enabled', true)) {
            Log::error('Outbound webhook retry job failed', [
                'delivery_id' => $this->delivery->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
