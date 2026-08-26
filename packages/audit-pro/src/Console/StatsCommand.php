<?php

declare(strict_types=1);

namespace MageTech\Audit\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use MageTech\Audit\Models\Audit;

class StatsCommand extends Command
{
    protected $signature = 'audit:stats
                    {--from= : Start date (Y-m-d)}
                    {--to= : End date (Y-m-d)}
                    {--tenant= : Filter by tenant ID}';

    protected $description = 'Display audit statistics';

    public function handle(): int
    {
        $this->info('MTS Laravel Audit Pro - Statistics');
        $this->newLine();

        $query = Audit::query();

        if ($from = $this->option('from')) {
            $query->where('created_at', '>=', $from);
        }

        if ($to = $this->option('to')) {
            $query->where('created_at', '<=', $to);
        }

        if ($tenant = $this->option('tenant')) {
            $query->where('tenant_id', $tenant);
        }

        $totalEvents = (clone $query)->count();
        $eventsToday = (clone $query)->whereDate('created_at', today())->count();
        $activeActors = (clone $query)->distinct('actor_type', 'actor_id')->count();

        $topEvents = (clone $query)
            ->select('event', DB::raw('count(*) as count'))
            ->groupBy('event')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $topModels = (clone $query)
            ->select('auditable_type', DB::raw('count(*) as count'))
            ->whereNotNull('auditable_type')
            ->groupBy('auditable_type')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $topActors = (clone $query)
            ->select('actor_type', 'actor_id', 'actor_name', DB::raw('count(*) as count'))
            ->whereNotNull('actor_type')
            ->groupBy('actor_type', 'actor_id', 'actor_name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $recentActivity = (clone $query)
            ->latest()
            ->limit(10)
            ->get();

        $securityEvents = (clone $query)
            ->whereIn('event', ['failed_login', 'permission_changed', 'role_changed'])
            ->count();

        $failedActions = (clone $query)
            ->where('event', 'failed_login')
            ->count();

        // Display stats
        $this->line('Overview:');
        $this->line("  Total Events:      {$totalEvents}");
        $this->line("  Events Today:      {$eventsToday}");
        $this->line("  Active Actors:     {$activeActors}");
        $this->line("  Security Events:   {$securityEvents}");
        $this->line("  Failed Actions:    {$failedActions}");
        $this->newLine();

        // Top Events
        $this->info('Top Event Types:');
        foreach ($topEvents as $event) {
            $bar = str_repeat('█', min((int) ($event->count / max($topEvents->first()->count, 1) * 30), 30));
            $this->line("  {$event->event}: {$event->count} {$bar}");
        }
        $this->newLine();

        // Top Models
        $this->info('Most Audited Models:');
        foreach ($topModels as $model) {
            $shortName = class_basename($model->auditable_type);
            $this->line("  {$shortName}: {$model->count}");
        }
        $this->newLine();

        // Top Actors
        $this->info('Top Actors:');
        foreach ($topActors as $actor) {
            $name = $actor->actor_name ?? $actor->actor_id ?? 'Unknown';
            $type = class_basename($actor->actor_type);
            $this->line("  {$name} ({$type}): {$actor->count}");
        }
        $this->newLine();

        // Recent Activity
        $this->info('Recent Activity:');
        foreach ($recentActivity as $activity) {
            $actor = $activity->actor_name ?? $activity->actor_type ?? 'System';
            $model = $activity->auditable_type ? class_basename($activity->auditable_type) : '-';
            $this->line("  [{$activity->created_at->format('Y-m-d H:i:s')}] {$actor} - {$activity->event} - {$model}");
        }
        $this->newLine();

        $this->line('Developed by MageTech Solutions - https://www.magetechsol.com/');

        return self::SUCCESS;
    }
}
