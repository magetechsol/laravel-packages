<?php

declare(strict_types=1);

namespace Tests\Unit\Validators;

use MageTech\ImportExport\Validators\RowValidator;

it('validates required fields', function () {
    $validator = new RowValidator([
        'name' => ['required'],
        'email' => ['required', 'email'],
    ]);

    $errors = $validator->validate(['name' => '', 'email' => '']);
    expect($errors)->toHaveKeys(['name', 'email']);
});

it('validates email format', function () {
    $validator = new RowValidator(['email' => ['email']]);

    $errors = $validator->validate(['email' => 'not-an-email']);
    expect($errors)->toHaveKey('email');

    $errors = $validator->validate(['email' => 'valid@example.com']);
    expect($errors)->toBeEmpty();
});

it('validates numeric fields', function () {
    $validator = new RowValidator(['price' => ['numeric']]);

    $errors = $validator->validate(['price' => 'abc']);
    expect($errors)->toHaveKey('price');

    $errors = $validator->validate(['price' => '29.99']);
    expect($errors)->toBeEmpty();
});

it('validates max length', function () {
    $validator = new RowValidator(['name' => ['max:5']]);

    $errors = $validator->validate(['name' => 'Long Name']);
    expect($errors)->toHaveKey('name');

    $errors = $validator->validate(['name' => 'Short']);
    expect($errors)->toBeEmpty();
});

it('validates in list', function () {
    $validator = new RowValidator(['status' => ['in:active,inactive']]);

    $errors = $validator->validate(['status' => 'pending']);
    expect($errors)->toHaveKey('status');

    $errors = $validator->validate(['status' => 'active']);
    expect($errors)->toBeEmpty();
});

it('skips validation for empty non-required fields', function () {
    $validator = new RowValidator(['email' => ['email']]);

    $errors = $validator->validate(['email' => '']);
    expect($errors)->toBeEmpty();
});
