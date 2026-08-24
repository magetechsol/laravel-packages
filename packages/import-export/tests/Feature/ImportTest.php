<?php

declare(strict_types=1);

namespace Tests\Feature;

use MageTech\ImportExport\Enums\ImportStatus;
use MageTech\ImportExport\Import;
use MageTech\ImportExport\Models\Import as ImportModel;
use MageTech\ImportExport\Tests\Fixtures\Product;

it('creates an import record from CSV file', function () {
    $path = $this->getFixturePath('products.csv');

    $import = Import::make(Product::class)
        ->from($path)
        ->map([
            'name' => 'name',
            'sku' => 'sku',
            'price' => 'price',
            'stock_quantity' => 'stock_quantity',
        ])
        ->process();

    expect($import)->toBeInstanceOf(ImportModel::class);
    expect($import->status)->toBe(ImportStatus::Completed);
    expect($import->successful_rows)->toBeGreaterThan(0);
    expect($import->file_type)->toBe('csv');
});

it('imports data into model correctly', function () {
    $path = $this->getFixturePath('products.csv');

    Import::make(Product::class)
        ->from($path)
        ->map([
            'name' => 'name',
            'sku' => 'sku',
            'price' => 'price',
            'stock_quantity' => 'stock_quantity',
        ])
        ->process();

    expect(Product::count())->toBe(3);

    $product = Product::where('sku', 'WGT-001')->first();
    expect($product)->not->toBeNull();
    expect($product->name)->toBe('Widget A');
    expect($product->price)->toBe('29.99');
});

it('validates rows during import', function () {
    $path = $this->getFixturePath('products.csv');

    $import = Import::make(Product::class)
        ->from($path)
        ->map([
            'name' => 'name',
            'sku' => 'sku',
            'price' => 'price',
        ])
        ->validate([
            'name' => ['required'],
            'price' => ['numeric'],
        ])
        ->process();

    expect($import->status)->toBe(ImportStatus::Completed);
});

it('queues an import successfully', function () {
    $path = $this->getFixturePath('products.csv');

    $import = Import::make(Product::class)
        ->from($path)
        ->map([
            'name' => 'name',
            'sku' => 'sku',
            'price' => 'price',
            'stock_quantity' => 'stock_quantity',
        ])
        ->queue();

    expect($import)->toBeInstanceOf(ImportModel::class);
    expect($import->status)->toBe(ImportStatus::Queued);
});
