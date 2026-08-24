<?php

declare(strict_types=1);

use MageTech\ApiToolkit\Enums\ErrorSeverity;

test('error severity returns correct label', function () {
    expect(ErrorSeverity::LOW->label())->toBe('Low')
        ->and(ErrorSeverity::MEDIUM->label())->toBe('Medium')
        ->and(ErrorSeverity::HIGH->label())->toBe('High')
        ->and(ErrorSeverity::CRITICAL->label())->toBe('Critical');
});

test('error severity returns correct description', function () {
    expect(ErrorSeverity::LOW->description())->toBe('Minor issues that do not affect functionality.')
        ->and(ErrorSeverity::MEDIUM->description())->toBe('Issues that may affect functionality under certain conditions.')
        ->and(ErrorSeverity::HIGH->description())->toBe('Issues that significantly impact functionality.')
        ->and(ErrorSeverity::CRITICAL->description())->toBe('Critical issues that prevent functionality entirely.');
});

test('error severity correctly determines logging', function () {
    expect(ErrorSeverity::LOW->shouldLog())->toBeFalse()
        ->and(ErrorSeverity::MEDIUM->shouldLog())->toBeTrue()
        ->and(ErrorSeverity::HIGH->shouldLog())->toBeTrue()
        ->and(ErrorSeverity::CRITICAL->shouldLog())->toBeTrue();
});

test('error severity correctly determines alerting', function () {
    expect(ErrorSeverity::LOW->shouldAlert())->toBeFalse()
        ->and(ErrorSeverity::MEDIUM->shouldAlert())->toBeFalse()
        ->and(ErrorSeverity::HIGH->shouldAlert())->toBeTrue()
        ->and(ErrorSeverity::CRITICAL->shouldAlert())->toBeTrue();
});
