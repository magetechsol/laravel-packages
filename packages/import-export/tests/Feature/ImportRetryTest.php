<?php

declare(strict_types=1);

namespace Tests\Feature;

use MageTech\ImportExport\Enums\ImportStatus;
use MageTech\ImportExport\Import;
use MageTech\ImportExport\Tests\Fixtures\Product;

it('retries failed rows', function () {
    $path = $this->getFixturePath('products.csv');

    $import = Import::make(Product::class)
        ->from($path)
        ->map(['name' => 'name', 'sku' => 'sku', 'price' => 'price'])
        ->validate(['price' => ['numeric', 'required']])
        ->process();

    expect($import->failed_rows)->toBe(0);

    expect($import->status)->toBe(ImportStatus::Completed);
});
