<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use MageTech\AIGateway\Concerns\GovernanceMiddleware;
use MageTech\AIGateway\Events\AiQuotaExceeded;
use MageTech\AIGateway\Exceptions\AiQuotaExceededException;
use MageTech\AIGateway\Models\AiUsage;
use Symfony\Component\HttpFoundation\Response;

class AiQuotaMiddleware
{
    use GovernanceMiddleware;

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('mts-ai.quotas.enabled', true)) {
            return $next($request);
        }

        $userId = $this->getUserId($request);
        $tenantId = $this->getTenantId($request);

        if ($tenantId) {
            $this->checkTenantDailyTokens($tenantId);
            $this->checkTenantMonthlyBudget($tenantId);
        }

        if ($userId) {
            $this->checkUserDailyRequests($userId);
        }

        $this->checkGlobalBudgets();

        return $next($request);
    }

    protected function checkTenantDailyTokens(int $tenantId): void
    {
        $limit = config('mts-ai.quotas.tenant_daily_tokens', 1000000);
        $current = AiUsage::getDailyTokens($tenantId);

        if ($current >= $limit) {
            event(new AiQuotaExceeded(
                userId: null,
                tenantId: $tenantId,
                quotaType: 'tenant_daily_tokens',
                currentUsage: (float) $current,
                limit: (float) $limit,
            ));

            throw AiQuotaExceededException::tenantTokenLimit($tenantId, $current, $limit);
        }
    }

    protected function checkTenantMonthlyBudget(int $tenantId): void
    {
        $limit = config('mts-ai.quotas.tenant_monthly_budget', 500.0);
        $current = AiUsage::getMonthlySpend($tenantId);

        if ($current >= $limit) {
            event(new AiQuotaExceeded(
                userId: null,
                tenantId: $tenantId,
                quotaType: 'tenant_monthly_budget',
                currentUsage: $current,
                limit: $limit,
            ));

            throw AiQuotaExceededException::tenantBudgetLimit($tenantId, $current, $limit);
        }
    }

    protected function checkUserDailyRequests(int $userId): void
    {
        $limit = config('mts-ai.quotas.user_daily_requests', 500);
        $current = AiUsage::getDailyRequests($userId);

        if ($current >= $limit) {
            event(new AiQuotaExceeded(
                userId: $userId,
                tenantId: null,
                quotaType: 'user_daily_requests',
                currentUsage: (float) $current,
                limit: (float) $limit,
            ));

            throw AiQuotaExceededException::userRequestLimit($userId, $current, $limit);
        }
    }

    protected function checkGlobalBudgets(): void
    {
        $dailyLimit = config('mts-ai.budgets.daily_limit', 1000.0);
        $monthlyLimit = config('mts-ai.budgets.monthly_limit', 10000.0);

        $dailySpend = (float) AiUsage::whereDate('date', now()->toDateString())->sum('estimated_cost');
        $monthlySpend = (float) AiUsage::whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->sum('estimated_cost');

        if ($dailySpend >= $dailyLimit) {
            throw AiQuotaExceededException::globalBudgetLimit('daily', $dailySpend, $dailyLimit);
        }

        if ($monthlySpend >= $monthlyLimit) {
            throw AiQuotaExceededException::globalBudgetLimit('monthly', $monthlySpend, $monthlyLimit);
        }
    }
}
