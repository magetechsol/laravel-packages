<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Models;

use Illuminate\Database\Eloquent\Model;

class AiUsage extends Model
{
    protected $table = 'mts_ai_usage';

    protected $fillable = [
        'user_id',
        'tenant_id',
        'date',
        'provider',
        'model',
        'request_count',
        'input_tokens',
        'output_tokens',
        'total_tokens',
        'estimated_cost',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'request_count' => 'integer',
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
            'total_tokens' => 'integer',
            'estimated_cost' => 'float',
        ];
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeForProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    public function scopeForModel($query, string $model)
    {
        return $query->where('model', $model);
    }

    public function scopeForToday($query)
    {
        return $query->whereDate('date', now()->toDateString());
    }

    public function scopeForThisMonth($query)
    {
        return $query->whereYear('date', now()->year)
            ->whereMonth('date', now()->month);
    }

    public static function record(
        ?int $userId,
        ?int $tenantId,
        string $provider,
        string $model,
        int $inputTokens,
        int $outputTokens,
        float $estimatedCost,
    ): static {
        return static::updateOrCreate(
            [
                'user_id' => $userId,
                'tenant_id' => $tenantId,
                'date' => now()->toDateString(),
                'provider' => $provider,
                'model' => $model,
            ],
            [
                'request_count' => \DB::raw('request_count + 1'),
                'input_tokens' => \DB::raw('input_tokens + ' . $inputTokens),
                'output_tokens' => \DB::raw('output_tokens + ' . $outputTokens),
                'total_tokens' => \DB::raw('total_tokens + ' . ($inputTokens + $outputTokens)),
                'estimated_cost' => \DB::raw('estimated_cost + ' . $estimatedCost),
            ]
        );
    }

    public static function getDailyTokens(?int $tenantId): int
    {
        return (int) static::where('tenant_id', $tenantId)
            ->whereDate('date', now()->toDateString())
            ->sum('total_tokens');
    }

    public static function getMonthlySpend(?int $tenantId): float
    {
        return (float) static::where('tenant_id', $tenantId)
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->sum('estimated_cost');
    }

    public static function getDailyRequests(?int $userId): int
    {
        return (int) static::where('user_id', $userId)
            ->whereDate('date', now()->toDateString())
            ->sum('request_count');
    }
}
