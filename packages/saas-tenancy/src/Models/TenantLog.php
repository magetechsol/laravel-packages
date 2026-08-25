<?php

declare(strict_types=1);

namespace MageTech\SaaS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantLog extends Model
{
    protected $table = 'mts_tenant_activity';

    protected $fillable = [
        'tenant_id',
        'event',
        'description',
        'user_id',
        'ip_address',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(config('mts-saas.model', Tenant::class), 'tenant_id');
    }

    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeForEvent($query, string $event)
    {
        return $query->where('event', $event);
    }
}
