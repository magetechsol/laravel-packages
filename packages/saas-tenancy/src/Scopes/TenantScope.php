<?php

declare(strict_types=1);

namespace MageTech\SaaS\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $column = config('mts-saas.key_column', 'tenant_id');
        $tenantId = tenant_id();

        if ($tenantId && $builder->getModel()->isFillable($column)) {
            $builder->where($column, $tenantId);
        }
    }

    public function extend(Builder $builder): void
    {
        $builder->macro('withoutTenantScope', function (Builder $builder) {
            return $builder->withoutGlobalScope(TenantScope::class);
        });

        $builder->macro('forTenant', function (Builder $builder, string $tenantId) {
            $column = config('mts-saas.key_column', 'tenant_id');

            return $builder->withoutGlobalScope(TenantScope::class)
                ->where($column, $tenantId);
        });
    }
}
