<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Contracts;

use Illuminate\Http\Request;

interface WebhookProviderContract
{
    public function name(): string;

    public function verifySignature(Request $request, string $secret): bool;

    public function extractEventName(Request $request): ?string;

    public function extractIdempotencyKey(Request $request, array $payload): ?string;

    public function getSignatureHeader(): string;

    public function getTimestampFromRequest(Request $request): ?int;
}
