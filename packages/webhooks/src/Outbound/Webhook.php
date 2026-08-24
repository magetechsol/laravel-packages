<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Outbound;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use MageTech\Webhooks\Enums\DeliveryStatus;
use MageTech\Webhooks\Jobs\DeliverOutboundWebhookJob;
use MageTech\Webhooks\Models\WebhookDelivery;
use MageTech\Webhooks\Support\SensitiveDataMasker;

final class Webhook
{
    private ?string $eventName = null;
    private ?string $url = null;
    private array $payload = [];
    private ?string $secret = null;
    private array $headers = [];
    private ?int $maxAttempts = null;
    private bool $shouldQueue = false;
    private ?string $queueName = null;

    public static function send(string $eventName): static
    {
        $instance = new static();
        $instance->eventName = $eventName;

        return $instance;
    }

    public function to(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function payload(array|object $payload): static
    {
        $this->payload = is_object($payload) ? (array) $payload : $payload;

        return $this;
    }

    public function signWith(string $secret): static
    {
        $this->secret = $secret;

        return $this;
    }

    public function withHeaders(array $headers): static
    {
        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    public function maxAttempts(int $maxAttempts): static
    {
        $this->maxAttempts = $maxAttempts;

        return $this;
    }

    public function queue(?string $queueName = null): WebhookDelivery
    {
        $this->shouldQueue = true;
        $this->queueName = $queueName;

        return $this->dispatch();
    }

    public function now(): WebhookDelivery
    {
        $this->shouldQueue = false;

        return $this->dispatch();
    }

    private function dispatch(): WebhookDelivery
    {
        $this->validate();

        $maskedPayload = app(SensitiveDataMasker::class)->mask($this->payload);

        $signedHeaders = $this->buildHeaders();

        $delivery = DB::transaction(function () use ($signedHeaders) {
            return WebhookDelivery::create([
                'event_name' => $this->eventName,
                'url' => $this->url,
                'payload' => $maskedPayload,
                'headers' => $signedHeaders,
                'status' => DeliveryStatus::Pending,
                'attempts' => 0,
                'max_attempts' => $this->maxAttempts
                    ?? config('mts-webhooks.outbound.default_max_attempts', 5),
            ]);
        });

        if ($this->shouldQueue) {
            $job = new DeliverOutboundWebhookJob($delivery);

            $job->onQueue($this->queueName ?? config('mts-webhooks.outbound.queue', 'default'));

            $connection = config('mts-webhooks.outbound.connection');

            if ($connection !== null) {
                $job->onConnection($connection);
            }

            Queue::push($job);
        } else {
            app(DeliveryTracker::class)->deliver($delivery);
        }

        return $delivery;
    }

    private function validate(): void
    {
        if ($this->eventName === null) {
            throw new \InvalidArgumentException('Webhook event name is required');
        }

        if ($this->url === null) {
            throw new \InvalidArgumentException('Webhook URL is required');
        }
    }

    private function buildHeaders(): array
    {
        $headers = array_merge($this->headers, [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);

        if ($this->secret !== null) {
            $payloadJson = json_encode($this->payload, JSON_THROW_ON_ERROR);
            $signatureHeaders = app(Signer::class)->generateHeader($payloadJson, $this->secret);
            $headers = array_merge($headers, $signatureHeaders);
        }

        return $headers;
    }
}
