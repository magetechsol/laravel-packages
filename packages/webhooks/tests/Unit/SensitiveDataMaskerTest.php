<?php

declare(strict_types=1);

use MageTech\Webhooks\Support\SensitiveDataMasker;

it('masks sensitive fields in a flat array', function () {
    $masker = new SensitiveDataMasker();

    $data = [
        'name' => 'John',
        'password' => 'secret123',
        'token' => 'abc-def-ghi',
        'email' => 'john@example.com',
    ];

    $masked = $masker->mask($data);

    expect($masked['name'])->toBe('John');
    expect($masked['email'])->toBe('john@example.com');
    expect($masked['password'])->not->toBe('secret123');
    expect($masked['token'])->not->toBe('abc-def-ghi');
});

it('masks sensitive fields in nested arrays', function () {
    $masker = new SensitiveDataMasker();

    $data = [
        'user' => [
            'name' => 'John',
            'authorization' => 'Bearer token123',
        ],
        'card_number' => '4111111111111111',
    ];

    $masked = $masker->mask($data);

    expect($masked['user']['name'])->toBe('John');
    expect($masked['user']['authorization'])->not->toBe('Bearer token123');
    expect($masked['card_number'])->not->toBe('4111111111111111');
});

it('does not mask non-sensitive fields', function () {
    $masker = new SensitiveDataMasker();

    $data = [
        'event' => 'payment.success',
        'amount' => 1000,
        'currency' => 'USD',
    ];

    $masked = $masker->mask($data);

    expect($masked)->toBe($data);
});

it('masks short values completely', function () {
    $masker = new SensitiveDataMasker();

    $data = ['secret' => 'ab'];
    $masked = $masker->mask($data);

    expect($masked['secret'])->toBe('**');
});

it('preserves some characters for longer values', function () {
    $masker = new SensitiveDataMasker();

    $data = ['password' => 'longpassword'];
    $masked = $masker->mask($data);

    expect($masked['password'])->not->toBe('longpassword');
    expect(strlen($masked['password']))->toBe(12);
    expect(substr($masked['password'], 0, 3))->toBe('lon');
});
