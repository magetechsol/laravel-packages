<?php

declare(strict_types=1);

namespace Tests\Integration;

use MageTech\ImportExport\Export;
use MageTech\ImportExport\Import;
use MageTech\ImportExport\Tests\Fixtures\Product;

it('completes full import flow from CSV', function () {
    $path = $this->getFixturePath('products.csv');

    $import = Import::make(Product::class)
        ->from($path)
        ->map([
            'name' => 'name',
            'sku' => 'sku',
            'price' => 'price',
            'stock_quantity' => 'stock_quantity',
        ])
        ->validate([
            'name' => ['required'],
            'sku' => ['required'],
            'price' => ['numeric'],
        ])
        ->process();

    expect($import->status->value)->toBe('completed');
    expect($import->successful_rows)->toBe(3);
    expect($import->failed_rows)->toBe(0);
    expect(Product::count())->toBe(3);

    $product = Product::where('sku', 'WGT-001')->first();
    expect($product->name)->toBe('Widget A');
    expect($product->price)->toBe('29.99');
    expect((int) $product->stock_quantity)->toBe(100);
});

it('completes full export flow to CSV', function () {
    Product::create(['name' => 'Export Widget', 'sku' => 'EXP-001', 'price' => 99.99, 'stock_quantity' => 25]);

    $export = Export::make(Product::class)
        ->to('full_flow_test.csv')
        ->columns(['name', 'sku', 'price'])
        ->process();

    expect($export->status)->toBe('completed');
    expect($export->processed_rows)->toBe(1);
    expect(file_exists($export->file_path))->toBeTrue();

    $content = file_get_contents($export->file_path);
    expect($content)->toContain('Export Widget');
    expect($content)->toContain('EXP-001');

    @unlink($export->file_path);
});

it('handles import with transform callback', function () {
    $path = $this->getFixturePath('products.csv');

    Import::make(Product::class)
        ->from($path)
        ->map([
            'name' => 'name',
            'sku' => 'sku',
            'price' => 'price',
            'stock_quantity' => 'stock_quantity',
        ])
        ->transform(function (array $row) {
            $row['name'] = strtoupper($row['name']);
            $row['price'] = (string) ((float) $row['price'] * 1.1);

            return $row;
        })
        ->process();

    $product = Product::where('sku', 'WGT-001')->first();
    expect($product->name)->toBe('WIDGET A');
});
