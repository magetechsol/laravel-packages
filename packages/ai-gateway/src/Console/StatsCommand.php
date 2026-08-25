<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Console;

use Illuminate\Console\Command;
use MageTech\AIGateway\Models\AiLog;
use MageTech\AIGateway\Models\AiUsage;

class StatsCommand extends Command
{
    protected $signature = 'mts:ai:stats
        {--date= : Filter by date (Y-m-d)}
        {--provider= : Filter by provider}
        {--model= : Filter by model}
        {--tenant= : Filter by tenant ID}
        {--user= : Filter by user ID}';

    protected $description = 'Display AI Gateway usage statistics';

    public function handle(): int
    {
        $date = $this->option('date') ?? now()->toDateString();
        $provider = $this->option('provider');
        $model = $this->option('model');
        $tenantId = $this->option('tenant');
        $userId = $this->option('user');

        $this->info("AI Gateway Stats for {$date}");
        $this->newLine();

        $query = AiLog::whereDate('created_at', $date);

        if ($provider) {
            $query->where('provider', $provider);
        }

        if ($model) {
            $query->where('model', $model);
        }

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $totalRequests = $query->count();
        $successfulRequests = (clone $query)->where('status', 'success')->count();
        $failedRequests = (clone $query)->where('status', 'failed')->count();
        $totalTokens = (clone $query)->sum('total_tokens');
        $totalCost = (clone $query)->sum('estimated_cost');
        $avgDuration = (clone $query)->avg('duration_ms');

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Requests', number_format($totalRequests)],
                ['Successful', number_format($successfulRequests)],
                ['Failed', number_format($failedRequests)],
                ['Total Tokens', number_format($totalTokens)],
                ['Estimated Cost', '$' . number_format($totalCost, 6)],
                ['Avg Duration', number_format($avgDuration, 2) . 'ms'],
            ]
        );

        if (! $provider && ! $model) {
            $this->newLine();
            $this->info('Top Providers:');
            $providers = AiLog::whereDate('created_at', $date)
                ->selectRaw('provider, count(*) as count, sum(total_tokens) as tokens, sum(estimated_cost) as cost')
                ->groupBy('provider')
                ->orderByDesc('count')
                ->get();

            $this->table(
                ['Provider', 'Requests', 'Tokens', 'Cost'],
                $providers->map(fn ($p) => [
                    $p->provider,
                    number_format($p->count),
                    number_format($p->tokens),
                    '$' . number_format($p->cost, 6),
                ])
            );
        }

        return Command::SUCCESS;
    }
}
