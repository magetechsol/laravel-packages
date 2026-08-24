<?php

declare(strict_types=1);

namespace Tests\Unit\Readers;

use MageTech\ImportExport\Exceptions\ImportException;
use MageTech\ImportExport\Readers\CsvReader;

it('reads CSV file with headers', function () {
    $path = $this->getFixturePath('products.csv');

    $reader = new CsvReader;
    $reader->open($path);

    expect($reader->headers())->toBe(['name', 'sku', 'price', 'stock_quantity']);
    expect($reader->totalRows())->toBeGreaterThan(0);

    $rows = iterator_to_array($reader->rows());
    expect($rows)->not->toBeEmpty();
    expect($rows[2]['name'])->toBe('Widget A');
    expect($rows[2]['sku'])->toBe('WGT-001');

    $reader->close();
});

it('reads CSV file without headers', function () {
    $path = $this->getFixturePath('products.csv');

    $reader = new CsvReader(hasHeader: false);
    $reader->open($path);

    expect($reader->headers())->toBe([]);
    expect($reader->totalRows())->toBeGreaterThan(0);

    $reader->close();
});

it('throws exception for missing file', function () {
    $reader = new CsvReader;
    $reader->open('/nonexistent/file.csv');
})->throws(ImportException::class);
