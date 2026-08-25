<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MageTech\AIGateway\Enums\AiRequestStatus;

class AiLog extends Model
{
    protected $table = 'mts_ai_logs';

    protected $fillable = [
        'request_id',
        'correlation_id',
        'user_id',
        'tenant_id',
        'prompt_name',
        'prompt_version',
        'provider',
        'model',
        'input_tokens',
        'output_tokens',
        'total_tokens',
        'estimated_cost',
        'duration_ms',
        'status',
        'error_message',
        'metadata',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
            'total_tokens' => 'integer',
            'estimated_cost' => 'float',
            'duration_ms' => 'float',
            'status' => AiRequestStatus::class,
            'metadata' => 'array',
            'prompt_version' => 'integer',
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

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    public function scopeByModel($query, string $model)
    {
        return $query->where('model', $model);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', AiRequestStatus::Success);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', AiRequestStatus::Failed);
    }

    public function scopeForToday($query)
    {
        return $query->whereDate('created_at', now()->toDateString());
    }

    public function scopeForThisMonth($query)
    {
        return $query->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month);
    }
}
