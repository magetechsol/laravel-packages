<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Testing;

use MageTech\AIGateway\Ai;
use MageTech\AIGateway\DTOs\PromptTemplate;

class FakeAiGateway
{
    protected array $responses = [];

    protected array $recordedCalls = [];

    public function __construct(
        protected Ai $gateway,
    ) {}

    public function fake(array|callable $responses): static
    {
        if (is_array($responses)) {
            $this->gateway->fake($responses);
        } elseif (is_callable($responses)) {
            $this->gateway->fake($responses);
        }

        return $this;
    }

    public function prompt(string $name): FakePromptBuilder
    {
        $this->recordedCalls[] = [
            'type' => 'prompt',
            'name' => $name,
            'time' => microtime(true),
        ];

        return new FakePromptBuilder(
            template: new PromptTemplate(
                name: $name,
                version: 1,
                template: "Fake prompt: {$name}",
            ),
            gateway: $this->gateway,
        );
    }

    public function assertPrompted(string $name): void
    {
        $found = collect($this->recordedCalls)
            ->where('type', 'prompt')
            ->where('name', $name)
            ->isNotEmpty();

        if (! $found) {
            throw new \PHPUnit\Framework\AssertionFailedError(
                "Expected prompt [{$name}] was not dispatched."
            );
        }
    }

    public function assertNotPrompted(string $name): void
    {
        $found = collect($this->recordedCalls)
            ->where('type', 'prompt')
            ->where('name', $name)
            ->isNotEmpty();

        if ($found) {
            throw new \PHPUnit\Framework\AssertionFailedError(
                "Unexpected prompt [{$name}] was dispatched."
            );
        }
    }

    public function assertUsedModel(string $model): void
    {
        $this->gateway->assertUsedModel($model);
    }

    public function assertUsedProvider(string $provider): void
    {
        $this->gateway->assertUsedProvider($provider);
    }

    public function assertTokens(int $min, ?int $max = null): void
    {
        $this->gateway->assertTokens($min, $max);
    }

    public function getRecordedCalls(): array
    {
        return $this->recordedCalls;
    }

    public function restore(): void
    {
        $this->responses = [];
        $this->recordedCalls = [];
        $this->gateway->restore();
    }
}
