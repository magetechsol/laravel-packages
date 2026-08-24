<?php

declare(strict_types=1);

namespace Tests\Unit\Readers;

use MageTech\ImportExport\Exceptions\ImportException;
use MageTech\ImportExport\Readers\JsonReader;

it('reads JSON array file', function () {
    $path = $this->getFixturePath('products.json');

    $reader = new JsonReader;
    $reader->open($path);

    expect($reader->headers())->toBe(['name', 'sku', 'price', 'stock_quantity']);
    expect($reader->totalRows())->toBe(3);

    $rows = iterator_to_array($reader->rows());
    expect($rows)->toHaveCount(3);
    expect($rows[1]['name'])->toBe('Widget A');

    $reader->close();
});

it('throws exception for invalid JSON', function () {
    $path = $this->getFixturePath('invalid.txt');

    $reader = new JsonReader;
    $reader->open($path);
})->throws(ImportException::class);
