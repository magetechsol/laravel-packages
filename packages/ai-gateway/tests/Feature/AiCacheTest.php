<?php

declare(strict_types=1);

use MageTech\AIGateway\Support\Facades\AI;

it('returns cached responses when cache is enabled', function () {
    config(['mts-ai.cache.enabled' => true]);

    AI::fake(['content' => 'Response']);

    $result1 = AI::prompt('test-cache')
        ->with(['key' => 'value'])
        ->generate();

    $result2 = AI::prompt('test-cache')
        ->with(['key' => 'value'])
        ->generate();

    expect($result1)->toBe($result2);
});

it('does not cache when cache is disabled', function () {
    config(['mts-ai.cache.enabled' => false]);

    AI::fake(['content' => 'Response']);

    $result = AI::prompt('test-no-cache')
        ->with(['key' => 'value'])
        ->generate();

    expect($result)->toBe(['content' => 'Response']);
});
