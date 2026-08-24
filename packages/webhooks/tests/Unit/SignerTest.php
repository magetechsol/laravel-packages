<?php

declare(strict_types=1);

use MageTech\Webhooks\Outbound\Signer;

it('generates HMAC-SHA256 signature', function () {
    $signer = new Signer();

    $signature = $signer->sign('test payload', 'secret-key');

    expect($signature)->toBe(hash_hmac('sha256', 'test payload', 'secret-key'));
});

it('verifies a valid signature', function () {
    $signer = new Signer();

    $payload = 'test payload';
    $secret = 'secret-key';
    $signature = $signer->sign($payload, $secret);

    expect($signer->verify($payload, $secret, $signature))->toBeTrue();
});

it('rejects an invalid signature', function () {
    $signer = new Signer();

    expect($signer->verify('test payload', 'secret-key', 'invalid-signature'))->toBeFalse();
});

it('generates signature headers', function () {
    $signer = new Signer();

    $headers = $signer->generateHeader('test payload', 'secret-key');

    expect($headers)->toHaveKeys(['X-Webhook-Signature', 'X-Webhook-Timestamp', 'X-Webhook-Signed-Payload']);
    expect($headers['X-Webhook-Signature'])->toBe(hash_hmac('sha256', 'test payload', 'secret-key'));
});
