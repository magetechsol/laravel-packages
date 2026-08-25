<?php

declare(strict_types=1);

use MageTech\AIGateway\Cost\CostEstimator;

it('estimates cost for openai gpt-4o', function () {
    $estimator = app(CostEstimator::class);

    $cost = $estimator->estimate('openai', 'gpt-4o', 1000, 500);

    expect($cost)->toBeGreaterThan(0);
});

it('returns zero for unknown model', function () {
    $estimator = app(CostEstimator::class);

    $cost = $estimator->estimate('unknown', 'unknown-model', 1000, 500);

    expect($cost)->toBe(0.0);
});

it('gets input price', function () {
    $estimator = app(CostEstimator::class);

    $price = $estimator->getInputPrice('openai', 'gpt-4o');

    expect($price)->toBeFloat()
        ->and($price)->toBeGreaterThan(0);
});

it('gets output price', function () {
    $estimator = app(CostEstimator::class);

    $price = $estimator->getOutputPrice('openai', 'gpt-4o');

    expect($price)->toBeFloat()
        ->and($price)->toBeGreaterThan(0);
});

it('lists models for provider', function () {
    $estimator = app(CostEstimator::class);

    $models = $estimator->getModelsForProvider('openai');

    expect($models)->toBeArray()
        ->and($models)->toContain('gpt-4o');
});
