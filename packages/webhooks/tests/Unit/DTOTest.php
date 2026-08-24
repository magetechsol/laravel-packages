<?php

declare(strict_types=1);

use MageTech\Webhooks\DTOs\WebhookPayload;
use MageTech\Webhooks\DTOs\WebhookStats;
use MageTech\Webhooks\DTOs\DeliveryResult;

it('creates WebhookPayload from array', function () {
    $data = [
        'provider' => 'stripe',
        'event' => 'payment_intent.succeeded',
        'payload' => ['id' => 'pi_123', 'amount' => 1000],
        'headers' => ['Stripe-Signature' => 'sig_123'],
        'signature' => 'sig_123',
        'idempotency_key' => 'evt_123',
    ];

    $dto = WebhookPayload::fromArray($data);

    expect($dto->provider)->toBe('stripe');
    expect($dto->event)->toBe('payment_intent.succeeded');
    expect($dto->payload)->toBe(['id' => 'pi_123', 'amount' => 1000]);
    expect($dto->idempotencyKey)->toBe('evt_123');
});

it('converts WebhookPayload to array', function () {
    $dto = new WebhookPayload(
        provider: 'stripe',
        event: 'payment_intent.succeeded',
        payload: ['id' => 'pi_123'],
        headers: [],
    );

    $array = $dto->toArray();

    expect($array)->toHaveKeys(['provider', 'event', 'payload', 'headers', 'signature', 'idempotency_key']);
});

it('creates success DeliveryResult', function () {
    $result = DeliveryResult::success(200, 'OK', 1);

    expect($result->success)->toBeTrue();
    expect($result->responseCode)->toBe(200);
    expect($result->deliveryId)->toBe(1);
});

it('creates failure DeliveryResult', function () {
    $result = DeliveryResult::failure(500, 'Error', 'Server error');

    expect($result->success)->toBeFalse();
    expect($result->error)->toBe('Server error');
});
