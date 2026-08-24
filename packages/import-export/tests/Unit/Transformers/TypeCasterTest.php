<?php

declare(strict_types=1);

namespace Tests\Unit\Transformers;

use MageTech\ImportExport\Transformers\TypeCaster;

it('casts string values', function () {
    $caster = new TypeCaster;

    expect($caster->castValue(123, 'string'))->toBe('123');
    expect($caster->castValue(45.67, 'string'))->toBe('45.67');
});

it('casts integer values', function () {
    $caster = new TypeCaster;

    expect($caster->castValue('123', 'int'))->toBe(123);
    expect($caster->castValue('45.67', 'integer'))->toBe(45);
});

it('casts float values', function () {
    $caster = new TypeCaster;

    expect($caster->castValue('29.99', 'float'))->toBe(29.99);
    expect($caster->castValue('29.99', 'double'))->toBe(29.99);
});

it('casts boolean values', function () {
    $caster = new TypeCaster;

    expect($caster->castValue('true', 'bool'))->toBeTrue();
    expect($caster->castValue('1', 'boolean'))->toBeTrue();
    expect($caster->castValue('yes', 'bool'))->toBeTrue();
    expect($caster->castValue('false', 'bool'))->toBeFalse();
    expect($caster->castValue('0', 'bool'))->toBeFalse();
});

it('casts null and empty to null', function () {
    $caster = new TypeCaster;

    expect($caster->castValue(null, 'string'))->toBeNull();
    expect($caster->castValue('', 'int'))->toBeNull();
});

it('casts rows with type map', function () {
    $caster = new TypeCaster;

    $row = ['price' => '29.99', 'active' => 'yes', 'name' => 'Test'];
    $result = $caster->cast($row, ['price' => 'float', 'active' => 'bool']);

    expect($result['price'])->toBe(29.99);
    expect($result['active'])->toBeTrue();
    expect($result['name'])->toBe('Test');
});
