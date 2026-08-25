<?php

declare(strict_types=1);

namespace MageTech\AIGateway;

use MageTech\AIGateway\Cost\CostEstimator;
use MageTech\AIGateway\DTOs\ResolvedModel;
use MageTech\AIGateway\DTOs\UsageData;
use MageTech\AIGateway\Events\AiFallbackTriggered;
use MageTech\AIGateway\Events\AiPromptResolving;
use MageTech\AIGateway\Events\AiPromptResolved;
use MageTech\AIGateway\Events\AiRouted;
use MageTech\AIGateway\Events\AiSending;
use MageTech\AIGateway\Events\AiSent;
use MageTech\AIGateway\Exceptions\AiProviderFailedException;
use MageTech\AIGateway\Models\AiLog;
use MageTech\AIGateway\Prompts\PromptBuilder;
use MageTech\AIGateway\Prompts\PromptManager;
use MageTech\AIGateway\Routing\ModelRouter;
use Ramsey\Uuid\Uuid;

class Ai
{
    protected ?array $fakeResponses = null;

    protected array $recordedPrompts = [];

    protected array $recordedModels = [];

    protected array $recordedTokens = [];

    protected array $recordedProviders = [];

    public function __construct(
        protected PromptManager $promptManager,
        protected ModelRouter $router,
        protected CostEstimator $costEstimator,
    ) {}

    public function prompt(string $name): PromptBuilder
    {
        $this->recordedPrompts[] = $name;

        event(new AiPromptResolving($name));

        $template = $this->promptManager->get($name);

        event(new AiPromptResolved($template));

        return new PromptBuilder(
            template: $template,
            gateway: $this,
        );
    }

    public function send(
        string $prompt,
        ?string $provider = null,
        ?string $model = null,
        ?float $temperature = null,
        ?int $maxTokens = null,
        ?int $tenantId = null,
        ?int $userId = null,
        array $options = [],
    ): mixed {
        if ($this->fakeResponses !== null) {
            return $this->generateFakeResponse($prompt, $provider, $model);
        }

        $requestId = Uuid::uuid4()->toString();

        $resolved = $this->router->resolve(
            provider: $provider,
            model: $model,
            tenantId: $tenantId,
        );

        $this->recordedModels[] = $resolved->model;
        $this->recordedProviders[] = $resolved->provider;

        event(new AiRouted($resolved, $requestId));

        $startTime = microtime(true);

        try {
            event(new AiSending($resolved, $requestId));

            $response = $this->attemptWithFallback(
                prompt: $prompt,
                resolved: $resolved,
                temperature: $temperature,
                maxTokens: $maxTokens,
                options: $options,
                requestId: $requestId,
                startTime: $startTime,
                tenantId: $tenantId,
                userId: $userId,
            );

            event(new AiSent($resolved, $requestId, $response));

            return $response;

        } catch (\Throwable $e) {
            throw AiProviderFailedException::allProvidersFailed($resolved, $e);
        }
    }

    protected function attemptWithFallback(
        string $prompt,
        ResolvedModel $resolved,
        ?float $temperature,
        ?int $maxTokens,
        array $options,
        string $requestId,
        float $startTime,
        ?int $tenantId,
        ?int $userId,
    ): mixed {
        $chain = $this->router->getFallbackChain(
            provider: $resolved->provider,
            model: $resolved->model,
        );

        $lastException = null;

        foreach ($chain as $index => $attempt) {
            try {
                return $this->callProvider(
                    provider: $attempt['provider'],
                    model: $attempt['model'],
                    prompt: $prompt,
                    temperature: $temperature ?? $resolved->temperature,
                    maxTokens: $maxTokens ?? $resolved->maxTokens,
                    options: $options,
                );

            } catch (\Throwable $e) {
                $lastException = $e;

                $nextAttempt = $chain[$index + 1] ?? null;

                if ($nextAttempt) {
                    event(new AiFallbackTriggered(
                        fromProvider: $attempt['provider'],
                        toProvider: $nextAttempt['provider'],
                        error: $e->getMessage(),
                        requestId: $requestId,
                    ));
                }

                continue;
            }
        }

        throw $lastException;
    }

    protected function callProvider(
        string $provider,
        string $model,
        string $prompt,
        float $temperature,
        int $maxTokens,
        array $options,
    ): mixed {
        $sdk = app('ai');

        $textProvider = $sdk->textProvider($provider);

        return $textProvider->prompt($prompt)
            ->withModel($model)
            ->withProviderOptions([
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
                ...$options,
            ])
            ->generate();
    }

    public function recordUsage(UsageData $usage): void
    {
        $this->recordedTokens[] = $usage->totalTokens;

        if (config('mts-ai.audit.enabled')) {
            $this->persistAuditLog($usage);
        }
    }

    protected function persistAuditLog(UsageData $usage): void
    {
        $storage = config('mts-ai.audit.storage', 'database');

        if ($storage === 'database' || $storage === 'both') {
            AiLog::create([
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
            ]);
        }

        if ($storage === 'log' || $storage === 'both') {
            logger()->info('AI Gateway audit log', [
                'request_id' => $usage->requestId,
                'provider' => $usage->provider,
                'model' => $usage->model,
                'tokens' => $usage->totalTokens,
                'cost' => $usage->estimatedCost,
                'duration_ms' => $usage->durationMs,
                'status' => $usage->status->value,
            ]);
        }
    }

    protected function generateFakeResponse(
        string $prompt,
        ?string $provider,
        ?string $model,
    ): mixed {
        $response = $this->fakeResponses ?? ['content' => 'Fake AI response'];

        if (is_callable($response)) {
            return $response($prompt, $provider, $model);
        }

        return $response;
    }

    public function fake(?array $responses = null): void
    {
        $this->fakeResponses = $responses;
    }

    public function restore(): void
    {
        $this->fakeResponses = null;
        $this->recordedPrompts = [];
        $this->recordedModels = [];
        $this->recordedTokens = [];
        $this->recordedProviders = [];
    }

    public function assertPrompted(string $name): void
    {
        $found = in_array($name, $this->recordedPrompts, true);

        if (! $found) {
            throw new \PHPUnit\Framework\AssertionFailedError(
                "Expected prompt [{$name}] was not dispatched."
            );
        }
    }

    public function assertNotPrompted(string $name): void
    {
        $found = in_array($name, $this->recordedPrompts, true);

        if ($found) {
            throw new \PHPUnit\Framework\AssertionFailedError(
                "Unexpected prompt [{$name}] was dispatched."
            );
        }
    }

    public function assertUsedModel(string $model): void
    {
        $found = in_array($model, $this->recordedModels, true);

        if (! $found) {
            throw new \PHPUnit\Framework\AssertionFailedError(
                "Expected model [{$model}] was not used."
            );
        }
    }

    public function assertUsedProvider(string $provider): void
    {
        $found = in_array($provider, $this->recordedProviders, true);

        if (! $found) {
            throw new \PHPUnit\Framework\AssertionFailedError(
                "Expected provider [{$provider}] was not used."
            );
        }
    }

    public function assertTokens(int $min, ?int $max = null): void
    {
        if (empty($this->recordedTokens)) {
            throw new \PHPUnit\Framework\AssertionFailedError(
                'No tokens were recorded.'
            );
        }

        $total = array_sum($this->recordedTokens);

        if ($total < $min) {
            throw new \PHPUnit\Framework\AssertionFailedError(
                "Expected at least [{$min}] tokens, but got [{$total}]."
            );
        }

        if ($max !== null && $total > $max) {
            throw new \PHPUnit\Framework\AssertionFailedError(
                "Expected at most [{$max}] tokens, but got [{$total}]."
            );
        }
    }

    public function getRecordedPrompts(): array
    {
        return $this->recordedPrompts;
    }

    public function getRecordedModels(): array
    {
        return $this->recordedModels;
    }

    public function getRecordedTokens(): array
    {
        return $this->recordedTokens;
    }

    public function getRecordedProviders(): array
    {
        return $this->recordedProviders;
    }
}
