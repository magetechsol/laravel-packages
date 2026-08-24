<?php

declare(strict_types=1);

namespace Tests\Unit\Mappers;

use MageTech\ImportExport\Mappers\ColumnMapper;

it('maps columns correctly', function () {
    $mapper = new ColumnMapper(
        mapping: [
            'Product Name' => 'name',
            'SKU' => 'sku',
            'Product Price' => 'price',
        ],
    );

    $row = [
        'Product Name' => 'Widget',
        'SKU' => 'WGT-001',
        'Product Price' => 29.99,
        'Extra Column' => 'ignored',
    ];

    $mapped = $mapper->map($row);

    expect($mapped)->toBe([
        'name' => 'Widget',
        'sku' => 'WGT-001',
        'price' => 29.99,
    ]);
});

it('applies default values', function () {
    $mapper = new ColumnMapper(
        mapping: ['name' => 'name'],
        defaults: ['is_active' => true, 'stock' => 0],
    );

    $mapped = $mapper->map(['name' => 'Test']);

    expect($mapped['is_active'])->toBeTrue();
    expect($mapped['stock'])->toBe(0);
});

it('skips configured columns', function () {
    $mapper = new ColumnMapper(
        mapping: ['name' => 'name', 'internal_id' => 'internal_id'],
        skipColumns: ['internal_id'],
    );

    $mapped = $mapper->map(['name' => 'Test', 'internal_id' => 42]);

    expect($mapped)->toBe(['name' => 'Test']);
});

it('supports nested mapping with dot notation', function () {
    $mapper = new ColumnMapper(
        mapping: [
            'meta.color' => 'metadata.color',
            'meta.size' => 'metadata.size',
        ],
    );

    $mapped = $mapper->map(['meta.color' => 'red', 'meta.size' => 'large']);

    expect($mapped['metadata']['color'])->toBe('red');
    expect($mapped['metadata']['size'])->toBe('large');
});
