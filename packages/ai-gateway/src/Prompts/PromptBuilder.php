<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Prompts;

use Closure;
use MageTech\AIGateway\Ai;
use MageTech\AIGateway\DTOs\PromptTemplate;

class PromptBuilder
{
    protected array $variables = [];

    protected ?string $model = null;

    protected ?string $provider = null;

    protected ?float $temperature = null;

    protected ?int $maxTokens = null;

    protected ?int $tenantId = null;

    protected ?int $userId = null;

    protected ?string $correlationId = null;

    protected array $options = [];

    protected ?Closure $onSuccess = null;

    protected ?Closure $onError = null;

    public function __construct(
        public readonly PromptTemplate $template,
        protected Ai $gateway,
    ) {
        $this->model = $template->model;
        $this->temperature = $template->temperature;
        $this->maxTokens = $template->maxTokens;
    }

    public function with(array $variables): static
    {
        $this->variables = array_merge($this->variables, $variables);

        return $this;
    }

    public function usingModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function usingProvider(string $provider): static
    {
        $this->provider = $provider;

        return $this;
    }

    public function withTemperature(float $temperature): static
    {
        $this->temperature = $temperature;

        return $this;
    }

    public function withMaxTokens(int $maxTokens): static
    {
        $this->maxTokens = $maxTokens;

        return $this;
    }

    public function forTenant(?int $tenantId): static
    {
        $this->tenantId = $tenantId;

        return $this;
    }

    public function forUser(?int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function withCorrelationId(string $correlationId): static
    {
        $this->correlationId = $correlationId;

        return $this;
    }

    public function withOptions(array $options): static
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    public function onSuccess(Closure $callback): static
    {
        $this->onSuccess = $callback;

        return $this;
    }

    public function onError(Closure $callback): static
    {
        $this->onError = $callback;

        return $this;
    }

    public function generate(): mixed
    {
        $rendered = $this->template->render($this->variables);

        try {
            $response = $this->gateway->send(
                prompt: $rendered,
                provider: $this->provider,
                model: $this->model,
                temperature: $this->temperature,
                maxTokens: $this->maxTokens,
                tenantId: $this->tenantId,
                userId: $this->userId,
                options: $this->options,
            );

            if ($this->onSuccess) {
                ($this->onSuccess)($response, $this->template);
            }

            return $response;

        } catch (\Throwable $e) {
            if ($this->onError) {
                return ($this->onError)($e, $this->template);
            }

            throw $e;
        }
    }

    public function generateStream(): mixed
    {
        $rendered = $this->template->render($this->variables);

        try {
            $sdk = app('ai');

            $textProvider = $sdk->textProvider($this->provider);

            return $textProvider->prompt($rendered)
                ->withModel($this->model)
                ->withProviderOptions([
                    'temperature' => $this->temperature,
                    'max_tokens' => $this->maxTokens,
                    ...$this->options,
                ])
                ->stream();

        } catch (\Throwable $e) {
            if ($this->onError) {
                return ($this->onError)($e, $this->template);
            }

            throw $e;
        }
    }

    public function getRenderedPrompt(): string
    {
        return $this->template->render($this->variables);
    }

    public function toArray(): array
    {
        return [
            'template' => $this->template->toArray(),
            'variables' => $this->variables,
            'model' => $this->model,
            'provider' => $this->provider,
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxTokens,
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'correlation_id' => $this->correlationId,
        ];
    }
}
