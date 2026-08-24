<?php

declare(strict_types=1);

namespace Tests\Feature;

use MageTech\ImportExport\Import;
use MageTech\ImportExport\Tests\Fixtures\Product;

it('detects and skips duplicates in ignore mode', function () {
    $path = $this->getFixturePath('products.csv');

    Import::make(Product::class)
        ->from($path)
        ->map(['name' => 'name', 'sku' => 'sku', 'price' => 'price', 'stock_quantity' => 'stock_quantity'])
        ->duplicateDetection('ignore', 'sku')
        ->process();

    expect(Product::count())->toBe(3);

    Import::make(Product::class)
        ->from($path)
        ->map(['name' => 'name', 'sku' => 'sku', 'price' => 'price', 'stock_quantity' => 'stock_quantity'])
        ->duplicateDetection('ignore', 'sku')
        ->process();

    expect(Product::count())->toBe(3);
});

it('upserts duplicates in upsert mode', function () {
    $path = $this->getFixturePath('products.csv');

    Import::make(Product::class)
        ->from($path)
        ->map(['name' => 'name', 'sku' => 'sku', 'price' => 'price', 'stock_quantity' => 'stock_quantity'])
        ->duplicateDetection('upsert', 'sku')
        ->process();

    expect(Product::count())->toBe(3);

    $product = Product::where('sku', 'WGT-001')->first();
    expect($product->price)->toBe('29.99');

    $updatedCsv = "name,sku,price,stock_quantity\nUpdated Widget,WGT-001,39.99,100\n";
    $updatedPath = storage_path('app/test_updated_products.csv');
    file_put_contents($updatedPath, $updatedCsv);

    Import::make(Product::class)
        ->from($updatedPath)
        ->map(['name' => 'name', 'sku' => 'sku', 'price' => 'price', 'stock_quantity' => 'stock_quantity'])
        ->duplicateDetection('upsert', 'sku')
        ->process();

    $product = Product::where('sku', 'WGT-001')->first();
    expect($product->name)->toBe('Updated Widget');
    expect($product->price)->toBe('39.99');

    @unlink($updatedPath);
});
