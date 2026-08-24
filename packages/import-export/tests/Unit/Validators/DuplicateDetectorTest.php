<?php

declare(strict_types=1);

namespace Tests\Unit\Validators;

use MageTech\ImportExport\Validators\DuplicateDetector;

it('detects ignore mode', function () {
    $detector = new DuplicateDetector(mode: 'ignore');

    expect($detector->shouldSkip())->toBeTrue();
    expect($detector->shouldReject())->toBeFalse();
    expect($detector->shouldUpsert())->toBeFalse();
});

it('detects reject mode', function () {
    $detector = new DuplicateDetector(mode: 'reject');

    expect($detector->shouldSkip())->toBeFalse();
    expect($detector->shouldReject())->toBeTrue();
    expect($detector->shouldUpsert())->toBeFalse();
});

it('detects upsert mode', function () {
    $detector = new DuplicateDetector(mode: 'upsert');

    expect($detector->shouldSkip())->toBeFalse();
    expect($detector->shouldReject())->toBeFalse();
    expect($detector->shouldUpsert())->toBeTrue();
});
