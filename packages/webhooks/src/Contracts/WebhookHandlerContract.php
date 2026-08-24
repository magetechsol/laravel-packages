<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Contracts;

interface WebhookHandlerContract
{
    public function handle(array $payload, array $headers, string $event, string $provider): void;
}
