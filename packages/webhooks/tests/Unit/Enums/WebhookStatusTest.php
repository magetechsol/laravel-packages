<?php

declare(strict_types=1);

use MageTech\Webhooks\Enums\WebhookStatus;

it('has correct labels', function () {
    expect(WebhookStatus::Pending->label())->toBe('Pending');
    expect(WebhookStatus::Processing->label())->toBe('Processing');
    expect(WebhookStatus::Processed->label())->toBe('Processed');
    expect(WebhookStatus::Failed->label())->toBe('Failed');
    expect(WebhookStatus::Dead->label())->toBe('Dead Letter');
});

it('identifies active statuses', function () {
    expect(WebhookStatus::Pending->isActive())->toBeTrue();
    expect(WebhookStatus::Processing->isActive())->toBeTrue();
    expect(WebhookStatus::Processed->isActive())->toBeFalse();
    expect(WebhookStatus::Failed->isActive())->toBeFalse();
    expect(WebhookStatus::Dead->isActive())->toBeFalse();
});

it('identifies terminal statuses', function () {
    expect(WebhookStatus::Pending->isTerminal())->toBeFalse();
    expect(WebhookStatus::Processing->isTerminal())->toBeFalse();
    expect(WebhookStatus::Processed->isTerminal())->toBeTrue();
    expect(WebhookStatus::Failed->isTerminal())->toBeFalse();
    expect(WebhookStatus::Dead->isTerminal())->toBeTrue();
});

it('identifies retryable statuses', function () {
    expect(WebhookStatus::Pending->canRetry())->toBeFalse();
    expect(WebhookStatus::Processing->canRetry())->toBeFalse();
    expect(WebhookStatus::Processed->canRetry())->toBeFalse();
    expect(WebhookStatus::Failed->canRetry())->toBeTrue();
    expect(WebhookStatus::Dead->canRetry())->toBeFalse();
});
