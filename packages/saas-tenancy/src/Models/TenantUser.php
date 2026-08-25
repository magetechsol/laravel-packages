<?php

declare(strict_types=1);

namespace MageTech\SaaS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantUser extends Model
{
    protected $table = 'mts_tenant_users';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'role',
        'is_owner',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_owner' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(config('mts-saas.model', Tenant::class), 'tenant_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', \App\Models\User::class), 'user_id');
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'owner'], true);
    }

    public function isOwner(): bool
    {
        return $this->is_owner || $this->role === 'owner';
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeWithRole($query, string $role)
    {
        return $query->where('role', $role);
    }
}
