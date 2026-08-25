<?php

declare(strict_types=1);

use MageTech\AIGateway\DTOs\PromptTemplate;
use MageTech\AIGateway\Exceptions\AiPromptNotFoundException;
use MageTech\AIGateway\Models\Prompt;
use MageTech\AIGateway\Prompts\PromptManager;

it('creates a new prompt template', function () {
    $manager = app(PromptManager::class);

    $template = $manager->create([
        'name' => 'product-description',
        'template' => 'Write a description for {{ product }}',
        'variables' => ['product'],
        'model' => 'gpt-4o',
    ]);

    expect($template)->toBeInstanceOf(PromptTemplate::class)
        ->and($template->name)->toBe('product-description')
        ->and($template->version)->toBe(1);
});

it('retrieves a prompt by name', function () {
    Prompt::create([
        'name' => 'greeting',
        'version' => 1,
        'template' => 'Hello {{ name }}',
        'status' => 'active',
    ]);

    $manager = app(PromptManager::class);
    $template = $manager->get('greeting');

    expect($template->name)->toBe('greeting')
        ->and($template->version)->toBe(1);
});

it('retrieves a specific version', function () {
    Prompt::create(['name' => 'test', 'version' => 1, 'template' => 'V1', 'status' => 'active']);
    Prompt::create(['name' => 'test', 'version' => 2, 'template' => 'V2', 'status' => 'active']);

    $manager = app(PromptManager::class);
    $template = $manager->get('test', version: 2);

    expect($template->version)->toBe(2)
        ->and($template->template)->toBe('V2');
});

it('retrieves latest version by default', function () {
    Prompt::create(['name' => 'test', 'version' => 1, 'template' => 'V1', 'status' => 'active']);
    Prompt::create(['name' => 'test', 'version' => 3, 'template' => 'V3', 'status' => 'active']);
    Prompt::create(['name' => 'test', 'version' => 2, 'template' => 'V2', 'status' => 'active']);

    $manager = app(PromptManager::class);
    $template = $manager->get('test');

    expect($template->version)->toBe(3);
});

it('throws exception for missing prompt', function () {
    $manager = app(PromptManager::class);

    $manager->get('nonexistent');
})->throws(AiPromptNotFoundException::class);

it('lists all versions of a prompt', function () {
    Prompt::create(['name' => 'test', 'version' => 1, 'template' => 'V1', 'status' => 'active']);
    Prompt::create(['name' => 'test', 'version' => 2, 'template' => 'V2', 'status' => 'active']);

    $manager = app(PromptManager::class);
    $versions = $manager->all('test');

    expect($versions)->toHaveCount(2);
});

it('updates a prompt version', function () {
    Prompt::create(['name' => 'test', 'version' => 1, 'template' => 'Old', 'status' => 'active']);

    $manager = app(PromptManager::class);
    $template = $manager->update('test', 1, ['template' => 'New']);

    expect($template->template)->toBe('New');
});
