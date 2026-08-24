<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Console;

use Illuminate\Console\Command;
use MageTech\Webhooks\Models\Webhook;

class ReplayWebhookCommand extends Command
{
    protected $signature = 'mts:webhook:replay
                    {webhook? : The webhook ID to replay}
                    {--provider= : Filter by provider}
                    {--event= : Filter by event name}
                    {--from= : Start date (Y-m-d)}
                    {--to= : End date (Y-m-d)}
                    {--dry-run : Show what would be replayed without processing}';

    protected $description = 'Replay one or more failed/dead webhook events';

    public function handle(): int
    {
        $webhookId = $this->argument('webhook');

        if ($webhookId !== null) {
            return $this->replaySingle((int) $webhookId);
        }

        return $this->replayBatch();
    }

    private function replaySingle(int $webhookId): int
    {
        $webhook = Webhook::findOrFail($webhookId);

        if ($this->option('dry-run')) {
            $this->line('Would replay webhook #' . $webhook->id);
            $this->line('  Provider: ' . $webhook->provider);
            $this->line('  Event: ' . $webhook->event);
            $this->line('  Status: ' . $webhook->status->value);
            $this->line('  Attempts: ' . $webhook->attempts);

            return self::SUCCESS;
        }

        $newWebhook = Webhook::create([
            'provider' => $webhook->provider,
            'event' => $webhook->event,
            'signature' => $webhook->signature,
            'payload' => $webhook->payload,
            'headers' => $webhook->headers,
            'status' => 'pending',
            'attempts' => 0,
            'max_attempts' => $webhook->max_attempts,
            'idempotency_key' => 'replay_' . $webhook->id . '_' . time(),
            'request_id' => $webhook->request_id,
            'source_ip' => $webhook->source_ip,
        ]);

        \MageTech\Webhooks\Events\WebhookReplayed::dispatch($newWebhook);

        $this->info('Webhook #' . $webhook->id . ' replayed as #' . $newWebhook->id);

        return self::SUCCESS;
    }

    private function replayBatch(): int
    {
        $query = Webhook::whereIn('status', ['failed', 'dead']);

        if ($this->option('provider')) {
            $query->where('provider', $this->option('provider'));
        }

        if ($this->option('event')) {
            $query->where('event', $this->option('event'));
        }

        if ($this->option('from')) {
            $query->where('created_at', '>=', $this->option('from'));
        }

        if ($this->option('to')) {
            $query->where('created_at', '<=', $this->option('to'));
        }

        $webhooks = $query->get();

        if ($webhooks->isEmpty()) {
            $this->info('No webhooks found matching the criteria.');

            return self::SUCCESS;
        }

        $this->info('Found ' . $webhooks->count() . ' webhook(s) to replay.');

        if ($this->option('dry-run')) {
            foreach ($webhooks as $webhook) {
                $this->line('  #' . $webhook->id . ' - ' . $webhook->provider . '/' . $webhook->event . ' (' . $webhook->status->value . ')');
            }

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($webhooks->count());
        $bar->start();

        foreach ($webhooks as $webhook) {
            $newWebhook = Webhook::create([
                'provider' => $webhook->provider,
                'event' => $webhook->event,
                'signature' => $webhook->signature,
                'payload' => $webhook->payload,
                'headers' => $webhook->headers,
                'status' => 'pending',
                'attempts' => 0,
                'max_attempts' => $webhook->max_attempts,
                'idempotency_key' => 'replay_' . $webhook->id . '_' . time(),
                'request_id' => $webhook->request_id,
                'source_ip' => $webhook->source_ip,
            ]);

            \MageTech\Webhooks\Events\WebhookReplayed::dispatch($newWebhook);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info($webhooks->count() . ' webhook(s) replayed successfully.');

        return self::SUCCESS;
    }
}
