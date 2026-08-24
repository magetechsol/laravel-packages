<?php

declare(strict_types=1);

use MageTech\Webhooks\Enums\AttemptStatus;

it('has correct labels', function () {
    expect(AttemptStatus::Success->label())->toBe('Success');
    expect(AttemptStatus::Failed->label())->toBe('Failed');
});

it('returns correct values', function () {
    expect(AttemptStatus::Success->value)->toBe('success');
    expect(AttemptStatus::Failed->value)->toBe('failed');
});
