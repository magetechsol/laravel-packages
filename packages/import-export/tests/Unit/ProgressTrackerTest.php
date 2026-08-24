<?php

declare(strict_types=1);

namespace Tests\Unit;

use MageTech\ImportExport\DTOs\ImportProgress;
use MageTech\ImportExport\Enums\ImportStatus;

it('calculates percentage correctly', function () {
    $progress = new ImportProgress(
        totalRows: 100,
        processedRows: 50,
        successfulRows: 45,
        failedRows: 5,
        skippedRows: 0,
        status: ImportStatus::Processing,
    );

    expect($progress->percentage())->toBe(50.0);
});

it('returns zero percentage for empty import', function () {
    $progress = new ImportProgress(
        totalRows: 0,
        processedRows: 0,
        successfulRows: 0,
        failedRows: 0,
        skippedRows: 0,
        status: ImportStatus::Pending,
    );

    expect($progress->percentage())->toBe(0.0);
});

it('converts to array correctly', function () {
    $progress = new ImportProgress(
        totalRows: 200,
        processedRows: 100,
        successfulRows: 90,
        failedRows: 10,
        skippedRows: 0,
        status: ImportStatus::Processing,
    );

    $array = $progress->toArray();

    expect($array)->toBe([
        'total_rows' => 200,
        'processed_rows' => 100,
        'successful_rows' => 90,
        'failed_rows' => 10,
        'skipped_rows' => 0,
        'percentage' => 50.0,
        'status' => 'processing',
    ]);
});

it('converts to JSON', function () {
    $progress = new ImportProgress(
        totalRows: 100,
        processedRows: 100,
        successfulRows: 100,
        failedRows: 0,
        skippedRows: 0,
        status: ImportStatus::Completed,
    );

    $json = $progress->toJson();
    $decoded = json_decode($json, true);

    expect((float) $decoded['percentage'])->toBe(100.0);
    expect($decoded['status'])->toBe('completed');
});
