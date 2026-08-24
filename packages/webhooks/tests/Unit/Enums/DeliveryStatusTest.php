<?php

declare(strict_types=1);

use MageTech\Webhooks\Enums\DeliveryStatus;

it('has correct labels', function () {
    expect(DeliveryStatus::Pending->label())->toBe('Pending');
    expect(DeliveryStatus::Success->label())->toBe('Success');
    expect(DeliveryStatus::Failed->label())->toBe('Failed');
    expect(DeliveryStatus::Dead->label())->toBe('Dead Letter');
});

it('identifies terminal statuses', function () {
    expect(DeliveryStatus::Pending->isTerminal())->toBeFalse();
    expect(DeliveryStatus::Success->isTerminal())->toBeTrue();
    expect(DeliveryStatus::Failed->isTerminal())->toBeFalse();
    expect(DeliveryStatus::Dead->isTerminal())->toBeTrue();
});

it('identifies retryable statuses', function () {
    expect(DeliveryStatus::Pending->canRetry())->toBeFalse();
    expect(DeliveryStatus::Success->canRetry())->toBeFalse();
    expect(DeliveryStatus::Failed->canRetry())->toBeTrue();
    expect(DeliveryStatus::Dead->canRetry())->toBeFalse();
});
