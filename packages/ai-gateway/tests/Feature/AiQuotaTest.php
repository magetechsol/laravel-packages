<?php

declare(strict_types=1);

use MageTech\AIGateway\Exceptions\AiQuotaExceededException;
use MageTech\AIGateway\Models\AiUsage;

it('allows requests within quota', function () {
    config(['mts-ai.quotas.enabled' => true]);
    config(['mts-ai.quotas.user_daily_requests' => 10]);

    AiUsage::record(1, null, 'openai', 'gpt-4o', 100, 50, 0.001);

    $requests = AiUsage::getDailyRequests(1);

    expect($requests)->toBeLessThan(10);
});

it('detects user daily request limit exceeded', function () {
    config(['mts-ai.quotas.user_daily_requests' => 2]);

    AiUsage::record(1, null, 'openai', 'gpt-4o', 100, 50, 0.001);
    AiUsage::record(1, null, 'openai', 'gpt-4o', 100, 50, 0.001);

    $requests = AiUsage::getDailyRequests(1);

    expect($requests)->toBeGreaterThanOrEqual(2);
});

it('detects tenant daily token limit', function () {
    config(['mts-ai.quotas.tenant_daily_tokens' => 100]);

    AiUsage::record(null, 1, 'openai', 'gpt-4o', 50, 50, 0.001);

    $tokens = AiUsage::getDailyTokens(1);

    expect($tokens)->toBe(100);
});

it('detects tenant monthly budget exceeded', function () {
    config(['mts-ai.quotas.tenant_monthly_budget' => 0.001]);

    AiUsage::record(null, 1, 'openai', 'gpt-4o', 1000, 500, 0.002);

    $spend = AiUsage::getMonthlySpend(1);

    expect($spend)->toBeGreaterThanOrEqual(0.001);
});
