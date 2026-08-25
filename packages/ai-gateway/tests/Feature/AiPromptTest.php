<?php

declare(strict_types=1);

use MageTech\AIGateway\Models\Prompt;
use MageTech\AIGateway\Support\Facades\AI;

it('creates and uses a prompt template end-to-end', function () {
    Prompt::create([
        'name' => 'product-desc',
        'version' => 1,
        'template' => 'Write a product description for {{ product_name }} in {{ language }}',
        'variables' => ['product_name', 'language'],
        'model' => 'gpt-4o',
        'temperature' => 0.7,
        'status' => 'active',
    ]);

    AI::fake(['content' => 'Amazing product description']);

    $result = AI::prompt('product-desc')
        ->with([
            'product_name' => 'Widget Pro',
            'language' => 'English',
        ])
        ->generate();

    expect($result)->toBe(['content' => 'Amazing product description']);
    AI::assertPrompted('product-desc');
});

it('supports prompt versioning', function () {
    Prompt::create(['name' => 'test', 'version' => 1, 'template' => 'V1 prompt', 'status' => 'active']);
    Prompt::create(['name' => 'test', 'version' => 2, 'template' => 'V2 prompt', 'status' => 'active']);

    $builder = AI::prompt('test');

    expect($builder->template->version)->toBe(2)
        ->and($builder->template->template)->toBe('V2 prompt');
});

it('supports model override on prompt', function () {
    Prompt::create([
        'name' => 'test',
        'version' => 1,
        'template' => 'Hello',
        'model' => 'gpt-4o-mini',
        'status' => 'active',
    ]);

    AI::fake(['content' => 'Hi']);

    AI::prompt('test')
        ->usingModel('gpt-4o')
        ->generate();

    AI::assertUsedModel('gpt-4o');
});

it('supports provider override on prompt', function () {
    Prompt::create([
        'name' => 'test',
        'version' => 1,
        'template' => 'Hello',
        'status' => 'active',
    ]);

    AI::fake(['content' => 'Hi']);

    AI::prompt('test')
        ->usingProvider('anthropic')
        ->generate();

    AI::assertUsedProvider('anthropic');
});
