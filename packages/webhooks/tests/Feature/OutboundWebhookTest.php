<?php

declare(strict_types=1);

use MageTech\Webhooks\Models\Webhook;
use MageTech\Webhooks\Models\WebhookDelivery;
use MageTech\Webhooks\Outbound\Webhook;

it('creates an outbound webhook delivery record', function () {
    $delivery = Webhook::send('order.created')
        ->to('https://example.com/webhook')
        ->payload(['order_id' => 1, 'total' => 100])
        ->now();

    expect($delivery)->toBeInstanceOf(WebhookDelivery::class);
    expect($delivery->event_name)->toBe('order.created');
    expect($delivery->url)->toBe('https://example.com/webhook');
    expect($delivery->status->value)->toBe('pending');
});

it('validates required fields', function () {
    $this->expectException(\InvalidArgumentException::class);

    Webhook::send('order.created')
        ->now();
});

it('validates URL is required', function () {
    $this->expectException(\InvalidArgumentException::class);

    Webhook::send('order.created')
        ->payload(['id' => 1])
        ->now();
});

it('signs outbound webhook with HMAC', function () {
    $delivery = Webhook::send('order.created')
        ->to('https://example.com/webhook')
        ->payload(['order_id' => 1])
        ->signWith('my-secret')
        ->now();

    expect($delivery->headers)->toHaveKey('X-Webhook-Signature');
    expect($delivery->headers)->toHaveKey('X-Webhook-Timestamp');
    expect($delivery->headers)->toHaveKey('X-Webhook-Signed-Payload');
});
