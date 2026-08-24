<?php

declare(strict_types=1);

namespace Tests\Feature;

use MageTech\ImportExport\Import;
use MageTech\ImportExport\Tests\Fixtures\Product;

it('rejects unauthorized import attempts', function () {
    $path = $this->getFixturePath('products.csv');

    $import = Import::make(Product::class)
        ->from($path)
        ->map(['name' => 'name', 'sku' => 'sku', 'price' => 'price', 'stock_quantity' => 'stock_quantity'])
        ->withOptions(['created_by' => null])
        ->process();

    expect($import->created_by)->toBeNull();
    expect($import->status->value)->toBe('completed');
});
