<?php

declare(strict_types=1);

namespace Tests\Feature;

use MageTech\ImportExport\Exceptions\FileValidationException;
use MageTech\ImportExport\Exceptions\ImportException;
use MageTech\ImportExport\Import;
use MageTech\ImportExport\Tests\Fixtures\Product;

it('rejects invalid file extension', function () {
    $path = $this->getFixturePath('invalid.txt');

    Import::make(Product::class)
        ->from($path)
        ->process();
})->throws(FileValidationException::class);

it('rejects non-existent file', function () {
    Import::make(Product::class)
        ->from('/nonexistent/file.csv')
        ->process();
})->throws(ImportException::class);
