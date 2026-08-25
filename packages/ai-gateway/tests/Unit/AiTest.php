<?php

declare(strict_types=1);

use MageTech\AIGateway\Support\Facades\AI;
use MageTech\AIGateway\DTOs\PromptTemplate;
use MageTech\AIGateway\Models\Prompt;

it('resolves the AI facade from the container', function () {
    $ai = AI::getFacadeRoot();

    expect($ai)->toBeInstanceOf(\MageTech\AIGateway\Ai::class);
});

it('records prompts when using the facade', function () {
    Prompt::create([
        'name' => 'test-prompt',
        'version' => 1,
        'template' => 'Hello {{ name }}',
        'variables' => ['name'],
        'status' => 'active',
    ]);

    AI::fake(['content' => 'Hello World']);

    AI::prompt('test-prompt')
        ->with(['name' => 'World'])
        ->generate();

    AI::assertPrompted('test-prompt');
});

it('can fake AI responses', function () {
    Prompt::create([
        'name' => 'greeting',
        'version' => 1,
        'template' => 'Say hello to {{ name }}',
        'status' => 'active',
    ]);

    AI::fake(['content' => 'Fake response']);

    $result = AI::prompt('greeting')
        ->with(['name' => 'World'])
        ->generate();

    expect($result)->toBe(['content' => 'Fake response']);
});

it('can restore after fake', function () {
    AI::fake(['content' => 'fake']);
    AI::restore();

    expect(AI::getRecordedPrompts())->toBe([]);
});

it('asserts used model correctly', function () {
    Prompt::create([
        'name' => 'test',
        'version' => 1,
        'template' => 'Hello',
        'status' => 'active',
    ]);

    AI::fake(['content' => 'response']);

    AI::prompt('test')
        ->usingModel('gpt-4o')
        ->generate();

    AI::assertUsedModel('gpt-4o');
});
