<?php

declare(strict_types=1);

namespace Tests\Unit;

use MageTech\ImportExport\ErrorReport;
use MageTech\ImportExport\Models\Import;
use MageTech\ImportExport\Models\ImportError;

it('generates error report for import with errors', function () {
    $import = Import::create([
        'name' => 'test-import',
        'file_path' => '/tmp/test.csv',
        'file_name' => 'test.csv',
        'file_type' => 'csv',
        'status' => 'completed',
    ]);

    ImportError::create([
        'import_id' => $import->id,
        'row_number' => 2,
        'column' => 'email',
        'value' => 'invalid',
        'error' => 'Email is invalid',
    ]);

    ImportError::create([
        'import_id' => $import->id,
        'row_number' => 3,
        'column' => 'name',
        'value' => '',
        'error' => 'Name is required',
    ]);

    $report = new ErrorReport;
    $path = $report->generate($import);

    expect($path)->not->toBeNull();
    expect(file_exists($path))->toBeTrue();

    $content = file_get_contents($path);
    expect($content)->toContain('row_number');
    expect($content)->toContain('email');
    expect($content)->toContain('Email is invalid');

    @unlink($path);
});

it('returns null for import without errors', function () {
    $import = Import::create([
        'name' => 'clean-import',
        'file_path' => '/tmp/test.csv',
        'file_name' => 'test.csv',
        'file_type' => 'csv',
        'status' => 'completed',
    ]);

    $report = new ErrorReport;
    $path = $report->generate($import);

    expect($path)->toBeNull();
});

it('returns errors as array', function () {
    $import = Import::create([
        'name' => 'test-import',
        'file_path' => '/tmp/test.csv',
        'file_name' => 'test.csv',
        'file_type' => 'csv',
        'status' => 'completed',
    ]);

    ImportError::create([
        'import_id' => $import->id,
        'row_number' => 2,
        'column' => 'email',
        'error' => 'Email is invalid',
    ]);

    $report = new ErrorReport;
    $errors = $report->getErrorsAsArray($import);

    expect($errors)->toHaveCount(1);
    expect($errors[0]['column'])->toBe('email');
});
