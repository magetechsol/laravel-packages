<?php

declare(strict_types=1);

use MageTech\AIGateway\Security\PiiMasker;

it('masks email addresses', function () {
    $masker = app(PiiMasker::class);

    $result = $masker->mask('Contact me at john@example.com');

    expect($result)->toContain('*')
        ->and($result)->not->toContain('john@example.com');
});

it('masks phone numbers', function () {
    $masker = app(PiiMasker::class);

    $result = $masker->mask('Call me at 555-123-4567');

    expect($result)->toContain('*');
});

it('masks credit card numbers', function () {
    $masker = app(PiiMasker::class);

    $result = $masker->mask('Card: 4111-1111-1111-1111');

    expect($result)->toContain('*')
        ->and($result)->toContain('1111');
});

it('masks SSN', function () {
    $masker = app(PiiMasker::class);

    $result = $masker->mask('SSN: 123-45-6789');

    expect($result)->toContain('*')
        ->and($result)->toContain('6789');
});

it('preserves non-PII text', function () {
    $masker = app(PiiMasker::class);

    $result = $masker->mask('Hello World, this is a test');

    expect($result)->toBe('Hello World, this is a test');
});

it('can add custom patterns', function () {
    $masker = app(PiiMasker::class);

    $masker->addPattern('custom_id', '/\bCID-\d+\b/');

    expect($masker->hasPattern('custom_id'))->toBeTrue();
});

it('can remove patterns', function () {
    $masker = app(PiiMasker::class);

    $masker->removePattern('email');

    expect($masker->hasPattern('email'))->toBeFalse();
});
