<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Concerns;

use MageTech\Webhooks\Models\Webhook;

trait HandlesWebhooks
{
    public function webhook(): ?Webhook
    {
        return $this->webhook ?? null;
    }

    public function getWebhookPayload(): array
    {
        return $this->webhook?->payload ?? [];
    }

    public function getWebhookEvent(): ?string
    {
        return $this->webhook?->event;
    }

    public function getWebhookProvider(): ?string
    {
        return $this->webhook?->provider;
    }
}
