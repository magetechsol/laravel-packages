<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use MageTech\Webhooks\Models\Webhook;

class WebhookFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Webhook $webhook,
        public \Throwable $exception,
    ) {}
}
