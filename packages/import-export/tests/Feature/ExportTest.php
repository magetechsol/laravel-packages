<?php

declare(strict_types=1);

namespace Tests\Feature;

use MageTech\ImportExport\Export;
use MageTech\ImportExport\Tests\Fixtures\Product;

it('exports data to CSV', function () {
    Product::create(['name' => 'Widget', 'sku' => 'WGT-001', 'price' => 29.99, 'stock_quantity' => 100]);
    Product::create(['name' => 'Gadget', 'sku' => 'GDG-001', 'price' => 49.99, 'stock_quantity' => 50]);

    $export = Export::make(Product::class)
        ->to('test_export.csv')
        ->columns(['name', 'sku', 'price'])
        ->process();

    expect($export->file_path)->not->toBeNull();
    expect(file_exists($export->file_path))->toBeTrue();
    expect($export->processed_rows)->toBe(2);
    expect($export->status)->toBe('completed');

    $content = file_get_contents($export->file_path);
    expect($content)->toContain('Widget');
    expect($content)->toContain('Gadget');

    @unlink($export->file_path);
});

it('exports with filter', function () {
    Product::create(['name' => 'Active', 'sku' => 'ACT-001', 'price' => 10, 'stock_quantity' => 10, 'is_active' => true]);
    Product::create(['name' => 'Inactive', 'sku' => 'INA-001', 'price' => 20, 'stock_quantity' => 0, 'is_active' => false]);

    $export = Export::make(Product::class)
        ->to('filtered_export.csv')
        ->filter(fn ($q) => $q->where('is_active', true))
        ->process();

    expect($export->processed_rows)->toBe(1);

    $content = file_get_contents($export->file_path);
    expect($content)->toContain('Active');
    expect($content)->not->toContain('Inactive');

    @unlink($export->file_path);
});
