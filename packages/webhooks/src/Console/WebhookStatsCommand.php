<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Console;

use Illuminate\Console\Command;
use MageTech\Webhooks\DTOs\WebhookStats;

class WebhookStatsCommand extends Command
{
    protected $signature = 'mts:webhook:stats
                    {--days=30 : Number of days to include}
                    {--json : Output as JSON}';

    protected $description = 'Display webhook statistics and metrics';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $stats = WebhookStats::calculate($days);

        if ($this->option('json')) {
            $this->line(json_encode($stats->toArray(), JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        $this->info('Webhook Statistics (last ' . $days . ' days)');
        $this->line(str_repeat('-', 50));
        $this->line('Total Received:    ' . $stats->totalReceived);
        $this->line('Processed:         ' . $stats->processed);
        $this->line('Failed:            ' . $stats->failed);
        $this->line('Dead Lettered:     ' . $stats->deadLettered);
        $this->line('Pending:           ' . $stats->pending);
        $this->line('Processing:        ' . $stats->processing);
        $this->line('Success Rate:      ' . $stats->successRate . '%');
        $this->line(str_repeat('-', 50));

        if (! empty($stats->providerBreakdown)) {
            $this->info('By Provider:');
            foreach ($stats->providerBreakdown as $provider => $count) {
                $this->line('  ' . $provider . ': ' . $count);
            }
        }

        if (! empty($stats->eventBreakdown)) {
            $this->info('Top Events:');
            foreach ($stats->eventBreakdown as $event => $count) {
                $this->line('  ' . $event . ': ' . $count);
            }
        }

        if ($stats->lastReceivedAt !== null) {
            $this->line('');
            $this->line('Last Received: ' . $stats->lastReceivedAt->diffForHumans());
        }

        return self::SUCCESS;
    }
}
