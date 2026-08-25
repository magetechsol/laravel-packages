<?php

declare(strict_types=1);

namespace MageTech\SaaS\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MageTech\SaaS\Scopes\TenantScope;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model) {
            $column = config('mts-saas.key_column', 'tenant_id');

            if (! $model->{$column} && in_array($column, $model->getFillable(), true)) {
                $model->{$column} = tenant_id();
            }
        });
    }

    public function tenant(): BelongsTo
    {
        $tenantModel = config('mts-saas.model', \MageTech\SaaS\Models\Tenant::class);

        return $this->belongsTo($tenantModel, config('mts-saas.key_column', 'tenant_id'));
    }

    public function scopeForCurrentTenant(Builder $query): Builder
    {
        $column = config('mts-saas.key_column', 'tenant_id');
        $tenantId = tenant_id();

        if ($tenantId) {
            return $query->where($column, $tenantId);
        }

        return $query;
    }

    public function scopeWithoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }

    public function scopeForTenant(Builder $query, string $tenantId): Builder
    {
        $column = config('mts-saas.key_column', 'tenant_id');

        return $query->where($column, $tenantId);
    }
}
