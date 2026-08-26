<?php

declare(strict_types=1);

namespace MageTech\Audit\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Audit extends Model
{
    protected $guarded = [];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'changed_values' => 'array',
        'metadata' => 'array',
        'tags' => 'array',
        'created_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('audit.table', 'audits');

        if (config('audit.connection')) {
            $this->setConnection(config('audit.connection'));
        }
    }

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (Audit $audit) {
            if (empty($audit->uuid)) {
                $audit->uuid = (string) Str::uuid();
            }
        });
    }

    public function getTable(): string
    {
        return config('audit.table', 'audits');
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function actor(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeWhereEvent($query, string $event)
    {
        return $query->where('event', $event);
    }

    public function scopeWhereActor($query, string $type, int|string|null $id = null)
    {
        $query->where('actor_type', $type);

        if ($id !== null) {
            $query->where('actor_id', $id);
        }

        return $query;
    }

    public function scopeWhereModel($query, string $type, int|string|null $id = null)
    {
        $query->where('auditable_type', $type);

        if ($id !== null) {
            $query->where('auditable_id', $id);
        }

        return $query;
    }

    public function scopeWhereTenant($query, int|string|null $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeWhereDateRange($query, string $from, string $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    public function scopeWhereIp($query, string $ip)
    {
        return $query->where('ip_address', $ip);
    }

    public function scopeWhereRequestId($query, string $requestId)
    {
        return $query->where('request_id', $requestId);
    }

    public function scopeWhereBatch($query, string $batchUuid)
    {
        return $query->where('batch_uuid', $batchUuid);
    }

    public function scopeWhereTag($query, string $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    public function scopeWhereAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function getChangesAttribute(): ?array
    {
        if ($this->old_values === null || $this->new_values === null) {
            return null;
        }

        $changes = [];

        foreach ($this->new_values as $key => $newVal) {
            $oldVal = $this->old_values[$key] ?? null;

            if ($oldVal !== $newVal) {
                $changes[$key] = [
                    'old' => $oldVal,
                    'new' => $newVal,
                ];
            }
        }

        return $changes;
    }

    public function isEvent(string $event): bool
    {
        return $this->event === $event;
    }

    public function hasChanges(): bool
    {
        return !empty($this->changed_values) || ($this->old_values !== null && $this->new_values !== null);
    }

    public function getMetadataValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->metadata, $key, $default);
    }

    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags ?? [], true);
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        $array['changes'] = $this->changes;

        return $array;
    }
}
