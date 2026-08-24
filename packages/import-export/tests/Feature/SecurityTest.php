<?php

declare(strict_types=1);

namespace Tests\Feature;

use MageTech\ImportExport\Import;
use MageTech\ImportExport\Tests\Fixtures\Product;
use MageTech\ImportExport\Validators\FileValidator;

it('sanitizes CSV formula injection in imports', function () {
    $csvContent = "name,sku,price\n=SUM(A1:A10),WGT-001,29.99\n+CMD,WGD-002,19.99\nNormal Item,NRM-001,39.99\n";
    $path = storage_path('app/test_security_import.csv');
    file_put_contents($path, $csvContent);

    $import = Import::make(Product::class)
        ->from($path)
        ->map(['name' => 'name', 'sku' => 'sku', 'price' => 'price'])
        ->process();

    $product = Product::where('sku', 'WGT-001')->first();
    expect(str_starts_with($product->name, "'"))->toBeTrue();

    $product2 = Product::where('sku', 'NRM-001')->first();
    expect($product2->name)->toBe('Normal Item');

    @unlink($path);
});

it('validates file MIME types', function () {
    $validator1 = new FileValidator;
    $validator1->validateExtension('php');
    expect($validator1->isValid())->toBeFalse();

    $validator2 = new FileValidator;
    $validator2->validateExtension('csv');
    expect($validator2->isValid())->toBeTrue();
});

it('prevents path traversal', function () {
    $validator = new FileValidator(preventPathTraversal: true);

    $validator->validatePath('../../etc/passwd');
    expect($validator->isValid())->toBeFalse();
});
