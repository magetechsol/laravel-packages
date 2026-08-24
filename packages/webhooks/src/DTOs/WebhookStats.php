<?php

declare(strict_types=1);

namespace MageTech\Webhooks\DTOs;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final readonly class WebhookStats
{
    public function __construct(
        public int $totalReceived,
        public int $processed,
        public int $failed,
        public int $deadLettered,
        public int $pending,
        public int $processing,
        public float $successRate,
        public array $providerBreakdown,
        public array $eventBreakdown,
        public ?Carbon $lastReceivedAt,
    ) {}

    public static function calculate(?int $days = null): static
    {
        $query = DB::table('mts_webhooks');

        if ($days !== null) {
            $query->where('created_at', '>=', now()->subDays($days));
        }

        $total = (clone $query)->count();
        $processed = (clone $query)->where('status', 'processed')->count();
        $failed = (clone $query)->where('status', 'failed')->count();
        $dead = (clone $query)->where('status', 'dead')->count();
        $pending = (clone $query)->where('status', 'pending')->count();
        $processing = (clone $query)->where('status', 'processing')->count();

        $successRate = $total > 0 ? round(($processed / $total) * 100, 2) : 0.0;

        $providerBreakdown = (clone $query)
            ->select('provider', DB::raw('count(*) as count'))
            ->groupBy('provider')
            ->pluck('count', 'provider')
            ->toArray();

        $eventBreakdown = (clone $query)
            ->select('event', DB::raw('count(*) as count'))
            ->groupBy('event')
            ->orderByDesc('count')
            ->limit(10)
            ->pluck('count', 'event')
            ->toArray();

        $lastReceivedAt = DB::table('mts_webhooks')
            ->max('created_at');

        return new static(
            totalReceived: $total,
            processed: $processed,
            failed: $failed,
            deadLettered: $dead,
            pending: $pending,
            processing: $processing,
            successRate: $successRate,
            providerBreakdown: $providerBreakdown,
            eventBreakdown: $eventBreakdown,
            lastReceivedAt: $lastReceivedAt ? Carbon::parse($lastReceivedAt) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'total_received' => $this->totalReceived,
            'processed' => $this->processed,
            'failed' => $this->failed,
            'dead_lettered' => $this->deadLettered,
            'pending' => $this->pending,
            'processing' => $this->processing,
            'success_rate' => $this->successRate,
            'provider_breakdown' => $this->providerBreakdown,
            'event_breakdown' => $this->eventBreakdown,
            'last_received_at' => $this->lastReceivedAt?->toIso8601String(),
        ];
    }
}
