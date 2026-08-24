<?php

declare(strict_types=1);

namespace Tests\Integration;

use MageTech\ImportExport\Export;
use MageTech\ImportExport\Tests\Fixtures\Product;

it('exports all rows for large dataset', function () {
    for ($i = 1; $i <= 200; $i++) {
        Product::create([
            'name' => "Export Product {$i}",
            'sku' => 'EXP-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
            'price' => round(mt_rand(100, 5000) / 100, 2),
            'stock_quantity' => mt_rand(0, 100),
        ]);
    }

    $export = Export::make(Product::class)
        ->to('large_export.csv')
        ->chunkSize(50)
        ->process();

    expect($export->processed_rows)->toBe(200);
    expect($export->status)->toBe('completed');
    expect(file_exists($export->file_path))->toBeTrue();

    $lineCount = substr_count(file_get_contents($export->file_path), "\n");
    expect($lineCount)->toBeGreaterThanOrEqual(200);

    @unlink($export->file_path);
});
