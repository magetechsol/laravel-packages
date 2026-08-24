<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Inbound\Pipeline;

use Illuminate\Support\Facades\DB;
use MageTech\Webhooks\Exceptions\DuplicateWebhookException;
use MageTech\Webhooks\Models\Webhook;

class Deduplicator
{
    public function isDuplicate(Webhook $webhook): bool
    {
        $idempotencyKey = $webhook->idempotency_key;

        if ($idempotencyKey === null) {
            return false;
        }

        $existing = Webhook::where('idempotency_key', $idempotencyKey)
            ->where('id', '!=', $webhook->id)
            ->exists();

        if ($existing) {
            throw new DuplicateWebhookException(
                'Duplicate webhook detected with idempotency key: ' . $idempotencyKey
            );
        }

        return false;
    }

    public function ensureUnique(Webhook $webhook): void
    {
        $idempotencyKey = $webhook->idempotency_key;

        if ($idempotencyKey === null) {
            return;
        }

        $locked = DB::transaction(function () use ($idempotencyKey, $webhook) {
            $existing = Webhook::where('idempotency_key', $idempotencyKey)
                ->where('id', '!=', $webhook->id)
                ->lockForUpdate()
                ->first();

            if ($existing !== null) {
                throw new DuplicateWebhookException(
                    'Duplicate webhook detected with idempotency key: ' . $idempotencyKey
                );
            }

            return true;
        });
    }
}
