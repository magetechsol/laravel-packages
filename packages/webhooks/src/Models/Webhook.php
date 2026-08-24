<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use MageTech\Webhooks\Enums\WebhookStatus;

class Webhook extends Model
{
    use SoftDeletes;

    protected $table = 'mts_webhooks';

    protected $fillable = [
        'provider',
        'event',
        'signature',
        'payload',
        'headers',
        'status',
        'attempts',
        'max_attempts',
        'idempotency_key',
        'request_id',
        'source_ip',
        'processed_at',
        'failed_at',
        'dead_at',
        'error',
        'next_retry_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'headers' => 'array',
            'status' => WebhookStatus::class,
            'attempts' => 'integer',
            'max_attempts' => 'integer',
            'processed_at' => 'datetime',
            'failed_at' => 'datetime',
            'dead_at' => 'datetime',
            'next_retry_at' => 'datetime',
        ];
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(WebhookAttempt::class, 'webhook_id');
    }

    public function scopeProvider(Builder $query, string $provider): Builder
    {
        return $query->where('provider', $provider);
    }

    public function scopeEvent(Builder $query, string $event): Builder
    {
        return $query->where('event', $event);
    }

    public function scopeStatus(Builder $query, WebhookStatus|string $status): Builder
    {
        $value = $status instanceof WebhookStatus ? $status->value : $status;

        return $query->where('status', $value);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', WebhookStatus::Pending);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', WebhookStatus::Failed);
    }

    public function scopeReadyForRetry(Builder $query): Builder
    {
        return $query->where('status', WebhookStatus::Failed)
            ->where('attempts', '<', $this->attributes['max_attempts'] ?? 3)
            ->where(function (Builder $q) {
                $q->whereNull('next_retry_at')
                    ->orWhere('next_retry_at', '<=', now());
            });
    }

    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isTerminal(): bool
    {
        return $this->status->isTerminal();
    }

    public function canRetry(): bool
    {
        return $this->status->canRetry() && $this->attempts < $this->max_attempts;
    }

    public function markAsProcessing(): void
    {
        $this->update(['status' => WebhookStatus::Processing]);
    }

    public function markAsProcessed(): void
    {
        $this->update([
            'status' => WebhookStatus::Processed,
            'processed_at' => now(),
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => WebhookStatus::Failed,
            'failed_at' => now(),
            'error' => $errorMessage,
        ]);
    }

    public function markAsDead(string $errorMessage): void
    {
        $this->update([
            'status' => WebhookStatus::Dead,
            'dead_at' => now(),
            'error' => $errorMessage,
        ]);
    }

    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }

    public function scheduleRetry(\DateTimeInterface $nextRetryAt): void
    {
        $this->update(['next_retry_at' => $nextRetryAt]);
    }

    public function getIdempotencyKey(): ?string
    {
        return $this->idempotency_key;
    }
}
