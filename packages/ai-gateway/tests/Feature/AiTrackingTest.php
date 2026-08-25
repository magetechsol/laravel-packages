<?php

declare(strict_types=1);

use MageTech\AIGateway\DTOs\UsageData;
use MageTech\AIGateway\Enums\AiRequestStatus;
use MageTech\AIGateway\Models\AiLog;
use MageTech\AIGateway\Models\AiUsage;

it('records audit logs when enabled', function () {
    config(['mts-ai.audit.enabled' => true]);
    config(['mts-ai.audit.storage' => 'database']);

    $usage = new UsageData(
        requestId: 'test-request-123',
        userId: 1,
        tenantId: 1,
        provider: 'openai',
        model: 'gpt-4o',
        inputTokens: 100,
        outputTokens: 50,
        totalTokens: 150,
        estimatedCost: 0.0025,
        durationMs: 1234.5,
        status: AiRequestStatus::Success,
    );

    AiLog::create([
        'request_id' => $usage->requestId,
        'user_id' => $usage->userId,
        'tenant_id' => $usage->tenantId,
        'provider' => $usage->provider,
        'model' => $usage->model,
        'input_tokens' => $usage->inputTokens,
        'output_tokens' => $usage->outputTokens,
        'total_tokens' => $usage->totalTokens,
        'estimated_cost' => $usage->estimatedCost,
        'duration_ms' => $usage->durationMs,
        'status' => $usage->status->value,
    ]);

    $this->assertDatabaseHas('mts_ai_logs', [
        'request_id' => 'test-request-123',
        'provider' => 'openai',
        'model' => 'gpt-4o',
    ]);
});

it('records usage aggregation', function () {
    AiUsage::record(
        userId: 1,
        tenantId: 1,
        provider: 'openai',
        model: 'gpt-4o',
        inputTokens: 100,
        outputTokens: 50,
        estimatedCost: 0.0025,
    );

    $this->assertDatabaseHas('mts_ai_usage', [
        'user_id' => 1,
        'tenant_id' => 1,
        'provider' => 'openai',
        'model' => 'gpt-4o',
        'request_count' => 1,
        'total_tokens' => 150,
    ]);
});

it('increments usage on duplicate records', function () {
    AiUsage::record(1, 1, 'openai', 'gpt-4o', 100, 50, 0.0025);
    AiUsage::record(1, 1, 'openai', 'gpt-4o', 200, 100, 0.005);

    $usage = AiUsage::where('user_id', 1)
        ->where('provider', 'openai')
        ->first();

    expect($usage->request_count)->toBe(2)
        ->and($usage->total_tokens)->toBe(450);
});

it('queries daily tokens for tenant', function () {
    AiUsage::record(null, 1, 'openai', 'gpt-4o', 100, 50, 0.0025);

    $tokens = AiUsage::getDailyTokens(1);

    expect($tokens)->toBe(150);
});

it('queries monthly spend for tenant', function () {
    AiUsage::record(null, 1, 'openai', 'gpt-4o', 100, 50, 0.0025);

    $spend = AiUsage::getMonthlySpend(1);

    expect($spend)->toBeGreaterThan(0);
});

it('queries daily requests for user', function () {
    AiUsage::record(1, null, 'openai', 'gpt-4o', 100, 50, 0.0025);

    $requests = AiUsage::getDailyRequests(1);

    expect($requests)->toBe(1);
});
