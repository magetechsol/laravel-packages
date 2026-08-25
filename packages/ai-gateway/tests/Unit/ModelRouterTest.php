<?php

declare(strict_types=1);

use MageTech\AIGateway\DTOs\ResolvedModel;
use MageTech\AIGateway\Exceptions\AiConfigurationException;
use MageTech\AIGateway\Exceptions\AiModelNotAllowedException;
use MageTech\AIGateway\Routing\ModelRouter;

it('resolves default provider and model', function () {
    $router = app(ModelRouter::class);

    $resolved = $router->resolve();

    expect($resolved)->toBeInstanceOf(ResolvedModel::class)
        ->and($resolved->provider)->toBe('openai')
        ->and($resolved->model)->toBe('gpt-4o');
});

it('resolves custom provider and model', function () {
    $router = app(ModelRouter::class);

    $resolved = $router->resolve(provider: 'anthropic', model: 'claude-3-5-sonnet-20241022');

    expect($resolved->provider)->toBe('anthropic')
        ->and($resolved->model)->toBe('claude-3-5-sonnet-20241022');
});

it('throws exception for missing provider', function () {
    $router = app(ModelRouter::class);

    $router->resolve(provider: 'nonexistent');
})->throws(AiConfigurationException::class);

it('validates model against allowlist', function () {
    config(['mts-ai.models.allowlist' => true]);
    config(['mts-ai.models.fast' => ['gpt-4o-mini']]);
    config(['mts-ai.models.balanced' => ['gpt-4o']]);
    config(['mts-ai.models.premium' => ['claude-opus-4-20250514']]);

    $router = app(ModelRouter::class);

    expect($router->isAllowed('gpt-4o'))->toBeTrue()
        ->and($router->isAllowed('gpt-4o-mini'))->toBeTrue()
        ->and($router->isAllowed('unknown-model'))->toBeFalse();
});

it('returns models for tier', function () {
    config(['mts-ai.models.fast' => ['gpt-4o-mini', 'flash']]);

    $router = app(ModelRouter::class);

    expect($router->getModelsForTier('fast'))->toBe(['gpt-4o-mini', 'flash']);
});

it('gets fallback chain', function () {
    config(['mts-ai.routing.fallback_enabled' => true]);
    config(['mts-ai.routing.max_retries' => 3]);

    $router = app(ModelRouter::class);

    $chain = $router->getFallbackChain('openai', 'gpt-4o');

    expect($chain)->toBeArray()
        ->and($chain[0]['provider'])->toBe('openai')
        ->and($chain[0]['model'])->toBe('gpt-4o');
});
