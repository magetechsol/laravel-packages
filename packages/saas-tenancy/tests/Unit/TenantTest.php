<?php

declare(strict_types=1);

use MageTech\SaaS\Models\Tenant;
use MageTech\SaaS\Support\Facades\Tenant as TenantFacade;

it('creates a tenant', function () {
    $tenant = TenantFacade::create([
        'name' => 'Acme Corp',
        'slug' => 'acme',
        'domain' => 'acme.localhost',
        'status' => 'active',
    ]);

    expect($tenant)->toBeInstanceOf(Tenant::class)
        ->and($tenant->name)->toBe('Acme Corp')
        ->and($tenant->slug)->toBe('acme')
        ->and($tenant->status)->toBe('active');
});

it('auto-generates slug from name', function () {
    $tenant = TenantFacade::create([
        'name' => 'My Awesome Company',
        'status' => 'active',
    ]);

    expect($tenant->slug)->toBe('my-awesome-company');
});

it('sets default status to active', function () {
    $tenant = TenantFacade::create([
        'name' => 'Test',
        'slug' => 'test',
    ]);

    expect($tenant->status)->toBe('active')
        ->and($tenant->activated_at)->not->toBeNull();
});

it('identifies tenant by slug', function () {
    Tenant::create(['name' => 'Acme', 'slug' => 'acme', 'status' => 'active']);

    $tenant = TenantFacade::identify();

    expect($tenant)->toBeNull();
});

it('activates a suspended tenant', function () {
    $tenant = Tenant::create([
        'name' => 'Suspended',
        'slug' => 'suspended',
        'status' => 'suspended',
        'suspended_at' => now(),
    ]);

    TenantFacade::activate($tenant);

    expect($tenant->fresh()->status)->toBe('active');
});

it('suspends an active tenant', function () {
    $tenant = Tenant::create([
        'name' => 'Active',
        'slug' => 'active',
        'status' => 'active',
    ]);

    TenantFacade::suspend($tenant, 'Payment overdue');

    $fresh = $tenant->fresh();

    expect($fresh->status)->toBe('suspended')
        ->and($fresh->suspended_reason)->toBe('Payment overdue');
});

it('checks if tenant is active', function () {
    $tenant = Tenant::create(['name' => 'Active', 'slug' => 'active', 'status' => 'active']);

    expect($tenant->isActive())->toBeTrue()
        ->and($tenant->isSuspended())->toBeFalse();
});

it('lists all tenants', function () {
    Tenant::create(['name' => 'One', 'slug' => 'one', 'status' => 'active']);
    Tenant::create(['name' => 'Two', 'slug' => 'two', 'status' => 'active']);

    $tenants = Tenant::all();

    expect($tenants)->toHaveCount(2);
});

it('soft deletes a tenant', function () {
    $tenant = Tenant::create(['name' => 'Delete Me', 'slug' => 'delete-me', 'status' => 'active']);

    TenantFacade::delete($tenant);

    expect(Tenant::find($tenant->id))->toBeNull()
        ->and(Tenant::withTrashed()->find($tenant->id))->not->toBeNull();
});
