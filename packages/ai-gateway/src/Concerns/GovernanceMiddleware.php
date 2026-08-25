<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Concerns;

use Closure;
use Illuminate\Http\Request;
use MageTech\AIGateway\Models\AiUsage;

trait GovernanceMiddleware
{
    protected function getUserId(Request $request): ?int
    {
        return $request->user()?->id ?? $request->input('user_id');
    }

    protected function getTenantId(Request $request): ?int
    {
        return $request->user()?->tenant_id ?? $request->input('tenant_id');
    }

    protected function getApiKey(Request $request): ?string
    {
        return $request->bearerToken() ?? $request->input('api_key');
    }

    protected function getUsageForToday(?int $userId, ?int $tenantId): array
    {
        $usage = AiUsage::whereDate('date', now()->toDateString());

        if ($userId) {
            $usage->where('user_id', $userId);
        }

        if ($tenantId) {
            $usage->where('tenant_id', $tenantId);
        }

        $result = $usage->first();

        return [
            'requests' => $result?->request_count ?? 0,
            'tokens' => $result?->total_tokens ?? 0,
            'cost' => $result?->estimated_cost ?? 0.0,
        ];
    }

    protected function getMonthlySpend(?int $tenantId): float
    {
        return (float) AiUsage::where('tenant_id', $tenantId)
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->sum('estimated_cost');
    }
}
