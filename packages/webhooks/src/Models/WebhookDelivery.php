<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use MageTech\Webhooks\Enums\DeliveryStatus;

class WebhookDelivery extends Model
{
    use SoftDeletes;

    protected $table = 'mts_webhook_deliveries';

    protected $fillable = [
        'event_name',
        'url',
        'payload',
        'headers',
        'status',
        'attempts',
        'max_attempts',
        'response_code',
        'response_body',
        'error',
        'next_retry_at',
        'delivered_at',
        'dead_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'headers' => 'array',
            'status' => DeliveryStatus::class,
            'attempts' => 'integer',
            'max_attempts' => 'integer',
            'response_code' => 'integer',
            'next_retry_at' => 'datetime',
            'delivered_at' => 'datetime',
            'dead_at' => 'datetime',
        ];
    }

    public function scopeEventName(Builder $query, string $eventName): Builder
    {
        return $query->where('event_name', $eventName);
    }

    public function scopeStatus(Builder $query, DeliveryStatus|string $status): Builder
    {
        $value = $status instanceof DeliveryStatus ? $status->value : $status;

        return $query->where('status', $value);
    }

    public function scopeReadyForRetry(Builder $query): Builder
    {
        return $query->where('status', DeliveryStatus::Failed)
            ->where('attempts', '<', $this->attributes['max_attempts'] ?? 5)
            ->where(function (Builder $q) {
                $q->whereNull('next_retry_at')
                    ->orWhere('next_retry_at', '<=', now());
            });
    }

    public function isTerminal(): bool
    {
        return $this->status->isTerminal();
    }

    public function canRetry(): bool
    {
        return $this->status->canRetry() && $this->attempts < $this->max_attempts;
    }

    public function markAsSuccess(int $responseCode, string $responseBody): void
    {
        $this->update([
            'status' => DeliveryStatus::Success,
            'response_code' => $responseCode,
            'response_body' => $responseBody,
            'delivered_at' => now(),
        ]);
    }

    public function markAsFailed(int $responseCode, string $responseBody, string $errorMessage): void
    {
        $this->update([
            'status' => DeliveryStatus::Failed,
            'response_code' => $responseCode,
            'response_body' => $responseBody,
            'error' => $errorMessage,
        ]);
    }

    public function markAsDead(string $errorMessage): void
    {
        $this->update([
            'status' => DeliveryStatus::Dead,
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
}
