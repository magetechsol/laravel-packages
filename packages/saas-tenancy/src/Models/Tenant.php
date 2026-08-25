<?php

declare(strict_types=1);

namespace MageTech\SaaS\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use MageTech\SaaS\Events\TenantActivated;
use MageTech\SaaS\Events\TenantCreated;
use MageTech\SaaS\Events\TenantDeleted;
use MageTech\SaaS\Events\TenantSuspended;
use MageTech\SaaS\Scopes\TenantScope;

class Tenant extends Model
{
    use SoftDeletes;

    protected $table = 'mts_tenants';

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'database',
        'status',
        'settings',
        'metadata',
        'suspended_at',
        'suspended_reason',
        'activated_at',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'metadata' => 'array',
            'suspended_at' => 'datetime',
            'activated_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Tenant $tenant) {
            if (! $tenant->slug && $tenant->name) {
                $tenant->slug = \Str::slug($tenant->name);
            }

            if (! $tenant->status) {
                $tenant->status = 'active';
            }

            if (! $tenant->activated_at) {
                $tenant->activated_at = now();
            }
        });
    }

    public function users(): BelongsToMany
    {
        $pivotTable = config('mts-saas.users.table', 'mts_tenant_users');
        $tenantColumn = config('mts-saas.users.column', 'tenant_id');
        $userColumn = config('mts-saas.users.user_column', 'user_id');

        return $this->belongsToMany(
            config('auth.providers.users.model', \App\Models\User::class),
            $pivotTable,
            $tenantColumn,
            $userColumn,
        )->withPivot(config('mts-saas.users.role_column', 'role'));
    }

    public function logs(): HasMany
    {
        return $this->hasMany(TenantLog::class, 'tenant_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isDeleted(): bool
    {
        return $this->status === 'deleted';
    }

    public function activate(): void
    {
        $this->update([
            'status' => 'active',
            'activated_at' => now(),
            'suspended_at' => null,
            'suspended_reason' => null,
        ]);
    }

    public function suspend(?string $reason = null): void
    {
        $this->update([
            'status' => 'suspended',
            'suspended_at' => now(),
            'suspended_reason' => $reason,
        ]);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeSuspended(Builder $query): Builder
    {
        return $query->where('status', 'suspended');
    }

    public function scopeForDomain(Builder $query, string $domain): Builder
    {
        return $query->where('domain', $domain);
    }

    public function scopeForSlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', $slug);
    }

    public function getKeyType(): string
    {
        $type = config('mts-saas.key_type', 'uuid');

        return match ($type) {
            'uuid', 'ulid' => 'string',
            default => 'int',
        };
    }

    public function getIncrementing(): bool
    {
        return config('mts-saas.key_type', 'uuid') === 'int';
    }
}
