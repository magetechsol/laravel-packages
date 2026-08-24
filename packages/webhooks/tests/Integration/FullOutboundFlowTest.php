<?php

declare(strict_types=1);

use MageTech\Webhooks\Models\WebhookDelivery;
use MageTech\Webhooks\Outbound\Webhook;

it('creates and tracks outbound webhook delivery', function () {
    $delivery = Webhook::send('order.created')
        ->to('https://example.com/webhook')
        ->payload([
            'order_id' => 1,
            'total' => 100,
            'status' => 'completed',
        ])
        ->signWith('my-secret-key')
        ->withHeaders(['X-Custom' => 'value'])
        ->maxAttempts(3)
        ->now();

    expect($delivery)->toBeInstanceOf(WebhookDelivery::class);
    expect($delivery->event_name)->toBe('order.created');
    expect($delivery->url)->toBe('https://example.com/webhook');
    expect($delivery->payload)->toHaveKey('order_id');
    expect($delivery->headers)->toHaveKey('X-Webhook-Signature');
    expect($delivery->headers)->toHaveKey('X-Webhook-Timestamp');
    expect($delivery->max_attempts)->toBe(3);
});

it('creates outbound webhook with queue dispatch', function () {
    $delivery = Webhook::send('payment.completed')
        ->to('https://example.com/payment-webhook')
        ->payload(['payment_id' => 'pay_123'])
        ->queue();

    expect($delivery)->toBeInstanceOf(WebhookDelivery::class);
    expect($delivery->event_name)->toBe('payment.completed');
    expect($delivery->status->value)->toBe('pending');
});
