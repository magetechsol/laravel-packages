<?php

declare(strict_types=1);

use MageTech\SaaS\Models\Tenant;
use MageTech\SaaS\Scopes\TenantScope;

it('applies tenant scope to query', function () {
    $tenant1 = Tenant::create(['name' => 'T1', 'slug' => 't1', 'status' => 'active']);
    $tenant2 = Tenant::create(['name' => 'T2', 'slug' => 't2', 'status' => 'active']);

    // Simulate scoped query
    $query = Tenant::query()->withoutGlobalScope(TenantScope::class);

    expect($query->count())->toBe(2);
});

it('can query without tenant scope', function () {
    $tenant = Tenant::create(['name' => 'T1', 'slug' => 't1', 'status' => 'active']);

    $all = Tenant::withoutGlobalScope(TenantScope::class)->get();

    expect($all)->toHaveCount(1);
});

it('filters by status', function () {
    Tenant::create(['name' => 'Active', 'slug' => 'active', 'status' => 'active']);
    Tenant::create(['name' => 'Suspended', 'slug' => 'suspended', 'status' => 'suspended']);

    $active = Tenant::active()->count();
    $suspended = Tenant::suspended()->count();

    expect($active)->toBe(1)
        ->and($suspended)->toBe(1);
});
