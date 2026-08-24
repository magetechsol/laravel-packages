<?php

declare(strict_types=1);

namespace Tests\Feature;

use MageTech\ImportExport\Import;
use MageTech\ImportExport\Tests\Fixtures\Product;

it('handles large dataset import', function () {
    $rows = [];

    for ($i = 1; $i <= 100; $i++) {
        $rows[] = [
            'name' => "Product {$i}",
            'sku' => 'PRD-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
            'price' => (string) round(mt_rand(1000, 10000) / 100, 2),
            'stock_quantity' => (string) mt_rand(0, 500),
        ];
    }

    $csvContent = "name,sku,price,stock_quantity\n";
    foreach ($rows as $row) {
        $csvContent .= implode(',', $row)."\n";
    }

    $path = storage_path('app/test_large_import.csv');
    file_put_contents($path, $csvContent);

    $import = Import::make(Product::class)
        ->from($path)
        ->map([
            'name' => 'name',
            'sku' => 'sku',
            'price' => 'price',
            'stock_quantity' => 'stock_quantity',
        ])
        ->chunkSize(25)
        ->process();

    expect($import->successful_rows)->toBe(100);
    expect(Product::count())->toBe(100);

    @unlink($path);
});
