<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use MageTech\AIGateway\Concerns\GovernanceMiddleware;
use MageTech\AIGateway\Concerns\HasAiTracking;
use MageTech\AIGateway\DTOs\UsageData;
use MageTech\AIGateway\Enums\AiRequestStatus;
use MageTech\AIGateway\Events\AiPromptLogged;
use MageTech\AIGateway\Models\AiLog;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class AiAuditMiddleware
{
    use GovernanceMiddleware;
    use HasAiTracking;

    protected ?string $requestId = null;

    protected float $startTime = 0;

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('mts-ai.audit.enabled', true)) {
            return $next($request);
        }

        $this->requestId = Uuid::uuid4()->toString();
        $this->startTime = microtime(true);

        $request->merge(['_ai_request_id' => $this->requestId]);

        $response = $next($request);

        $this->logRequest($request, $response);

        return $response;
    }

    protected function logRequest(Request $request, Response $response): void
    {
        $duration = (microtime(true) - $this->startTime) * 1000;

        $status = $response->getStatusCode() >= 200 && $response->getStatusCode() < 300
            ? AiRequestStatus::Success
            : AiRequestStatus::Failed;

        $body = json_decode($response->getContent(), true);

        $usage = new UsageData(
            requestId: $this->requestId,
            userId: $this->getUserId($request),
            tenantId: $this->getTenantId($request),
            provider: $body['provider'] ?? 'unknown',
            model: $body['model'] ?? 'unknown',
            inputTokens: $body['usage']['input_tokens'] ?? 0,
            outputTokens: $body['usage']['output_tokens'] ?? 0,
            totalTokens: ($body['usage']['input_tokens'] ?? 0) + ($body['usage']['output_tokens'] ?? 0),
            estimatedCost: $body['estimated_cost'] ?? 0.0,
            durationMs: $duration,
            status: $status,
            ipAddress: $request->ip(),
        );

        if (config('mts-ai.audit.storage') === 'database' || config('mts-ai.audit.storage') === 'both') {
            $logData = $this->buildLogData($usage, $request);
            AiLog::create($logData);
        }

        if (config('mts-ai.audit.storage') === 'log' || config('mts-ai.audit.storage') === 'both') {
            logger()->info('AI Gateway request', [
                'request_id' => $this->requestId,
                'provider' => $usage->provider,
                'model' => $usage->model,
                'tokens' => $usage->totalTokens,
                'duration_ms' => $duration,
                'status' => $status->value,
            ]);
        }

        $this->trackUsage($usage);

        event(new AiPromptLogged($this->requestId, $usage->toArray()));
    }

    protected function buildLogData(UsageData $usage, Request $request): array
    {
        $data = [
            'request_id' => $usage->requestId,
            'correlation_id' => $usage->correlationId,
            'user_id' => $usage->userId,
            'tenant_id' => $usage->tenantId,
            'prompt_name' => $usage->promptName,
            'prompt_version' => $usage->promptVersion,
            'provider' => $usage->provider,
            'model' => $usage->model,
            'input_tokens' => $usage->inputTokens,
            'output_tokens' => $usage->outputTokens,
            'total_tokens' => $usage->totalTokens,
            'estimated_cost' => $usage->estimatedCost,
            'duration_ms' => $usage->durationMs,
            'status' => $usage->status->value,
            'error_message' => $usage->errorMessage,
            'metadata' => $usage->metadata,
            'ip_address' => $usage->ipAddress,
        ];

        if (config('mts-ai.audit.mask_pii', true)) {
            $data = $this->maskPiiFields($data);
        }

        return $data;
    }

    protected function maskPiiFields(array $data): array
    {
        $sensitiveFields = config('mts-ai.audit.sensitive_fields', []);

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = $this->maskValue($data[$field]);
            }

            if (isset($data['metadata'][$field]) && is_string($data['metadata'][$field])) {
                $data['metadata'][$field] = $this->maskValue($data['metadata'][$field]);
            }
        }

        return $data;
    }

    protected function maskValue(string $value): string
    {
        if (strlen($value) <= 4) {
            return str_repeat('*', strlen($value));
        }

        return substr($value, 0, 2) . str_repeat('*', strlen($value) - 4) . substr($value, -2);
    }
}
