<?php

declare(strict_types=1);

namespace Tests\Unit\Validators;

use MageTech\ImportExport\Validators\FileValidator;

it('validates CSV file extension', function () {
    $validator = new FileValidator;

    $validator->validateExtension('csv');
    expect($validator->isValid())->toBeTrue();
});

it('rejects invalid file extension', function () {
    $validator = new FileValidator;

    $validator->validateExtension('exe');
    expect($validator->isValid())->toBeFalse();
    expect($validator->errors())->not->toBeEmpty();
});

it('validates file size within limits', function () {
    $validator = new FileValidator(maxFileSize: 1024 * 1024);

    $validator->validateSize(500 * 1024);
    expect($validator->isValid())->toBeTrue();
});

it('rejects oversized files', function () {
    $validator = new FileValidator(maxFileSize: 1024 * 1024);

    $validator->validateSize(2 * 1024 * 1024);
    expect($validator->isValid())->toBeFalse();
});

it('detects file types correctly', function () {
    expect(FileValidator::detectFileType('data.csv'))->toBe('csv');
    expect(FileValidator::detectFileType('data.xlsx'))->toBe('xlsx');
    expect(FileValidator::detectFileType('data.json'))->toBe('json');
    expect(FileValidator::detectFileType('data.xml'))->toBe('xml');
    expect(FileValidator::detectFileType('data.txt'))->toBeNull();
    expect(FileValidator::detectFileType('data.exe'))->toBeNull();
});
