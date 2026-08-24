<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MageTech\Webhooks\Enums\AttemptStatus;

class WebhookAttempt extends Model
{
    protected $table = 'mts_webhook_attempts';

    protected $fillable = [
        'webhook_id',
        'attempt_number',
        'status',
        'payload',
        'error',
        'duration_ms',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'duration_ms' => 'integer',
            'status' => AttemptStatus::class,
        ];
    }

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class, 'webhook_id');
    }

    public function isSuccess(): bool
    {
        return $this->status === AttemptStatus::Success;
    }

    public function isFailed(): bool
    {
        return $this->status === AttemptStatus::Failed;
    }
}
