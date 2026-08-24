<?php

declare(strict_types=1);

namespace Tests\Feature;

use MageTech\ImportExport\Enums\ImportStatus;
use MageTech\ImportExport\Import;
use MageTech\ImportExport\Models\Import as ImportModel;
use MageTech\ImportExport\Tests\Fixtures\Product;

it('queues import to sync driver', function () {
    $path = $this->getFixturePath('products.csv');

    $import = Import::make(Product::class)
        ->from($path)
        ->map(['name' => 'name', 'sku' => 'sku', 'price' => 'price', 'stock_quantity' => 'stock_quantity'])
        ->onConnection('sync')
        ->queue();

    expect($import->fresh()->status)->toBe(ImportStatus::Completed);
    expect(Product::count())->toBe(3);
});

it('handles queue failure gracefully', function () {
    $import = ImportModel::create([
        'name' => 'failing-import',
        'file_path' => '/nonexistent/file.csv',
        'file_name' => 'file.csv',
        'file_type' => 'csv',
        'status' => ImportStatus::Processing,
    ]);

    $import->markAsFailed('Simulated failure');

    expect($import->fresh()->status)->toBe(ImportStatus::Failed);
    expect($import->fresh()->error_summary)->toHaveKey('message');
});
