<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Console;

use Illuminate\Console\Command;
use MageTech\Webhooks\Enums\WebhookStatus;
use MageTech\Webhooks\Jobs\RetryInboundWebhookJob;
use MageTech\Webhooks\Models\Webhook;

class RetryWebhooksCommand extends Command
{
    protected $signature = 'mts:webhook:retry
                    {--provider= : Filter by provider}
                    {--limit=100 : Maximum number of webhooks to retry}';

    protected $description = 'Retry failed webhooks that are ready for retry';

    public function handle(): int
    {
        $query = Webhook::readyForRetry();

        if ($this->option('provider')) {
            $query->where('provider', $this->option('provider'));
        }

        $webhooks = $query->limit((int) $this->option('limit'))->get();

        if ($webhooks->isEmpty()) {
            $this->info('No webhooks ready for retry.');

            return self::SUCCESS;
        }

        $this->info('Dispatching ' . $webhooks->count() . ' webhook(s) for retry.');

        $bar = $this->output->createProgressBar($webhooks->count());
        $bar->start();

        foreach ($webhooks as $webhook) {
            RetryInboundWebhookJob::dispatch($webhook)
                ->onQueue(config('mts-webhooks.processing.queue', 'default'));

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        return self::SUCCESS;
    }
}
