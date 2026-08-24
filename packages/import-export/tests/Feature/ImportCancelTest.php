<?php

declare(strict_types=1);

namespace Tests\Feature;

use MageTech\ImportExport\Enums\ImportStatus;
use MageTech\ImportExport\Import;
use MageTech\ImportExport\Models\Import as ImportModel;
use MageTech\ImportExport\Tests\Fixtures\Product;

it('cancels a pending import', function () {
    $path = $this->getFixturePath('products.csv');

    $import = Import::make(Product::class)
        ->from($path)
        ->map(['name' => 'name', 'sku' => 'sku', 'price' => 'price', 'stock_quantity' => 'stock_quantity'])
        ->queue();

    expect($import->status)->toBe(ImportStatus::Queued);

    $import->markAsCancelled();

    expect($import->fresh()->status)->toBe(ImportStatus::Cancelled);
});

it('cannot cancel a completed import', function () {
    $import = ImportModel::create([
        'name' => 'completed-import',
        'file_path' => '/tmp/test.csv',
        'file_name' => 'test.csv',
        'file_type' => 'csv',
        'status' => ImportStatus::Completed,
    ]);

    expect($import->canCancel())->toBeFalse();
});
