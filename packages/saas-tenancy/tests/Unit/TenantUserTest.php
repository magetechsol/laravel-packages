<?php

declare(strict_types=1);

use MageTech\SaaS\Models\Tenant;
use MageTech\SaaS\Models\TenantUser;
use MageTech\SaaS\Tests\Models\User;

it('creates tenant user pivot record', function () {
    $tenant = Tenant::create(['name' => 'Acme', 'slug' => 'acme', 'status' => 'active']);
    $user = User::create(['name' => 'John', 'email' => 'john@example.com']);

    $tenantUser = TenantUser::create([
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
        'role' => 'admin',
        'is_owner' => true,
    ]);

    expect($tenantUser->role)->toBe('admin')
        ->and($tenantUser->is_owner)->toBeTrue();
});

it('checks if user is admin', function () {
    $tenant = Tenant::create(['name' => 'Acme', 'slug' => 'acme', 'status' => 'active']);
    $user = User::create(['name' => 'Admin', 'email' => 'admin@example.com']);

    $tenantUser = TenantUser::create([
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
        'role' => 'admin',
    ]);

    expect($tenantUser->isAdmin())->toBeTrue()
        ->and($tenantUser->hasRole('admin'))->toBeTrue()
        ->and($tenantUser->hasRole('member'))->toBeFalse();
});

it('queries users for tenant', function () {
    $tenant = Tenant::create(['name' => 'Acme', 'slug' => 'acme', 'status' => 'active']);
    $user1 = User::create(['name' => 'User1', 'email' => 'u1@example.com']);
    $user2 = User::create(['name' => 'User2', 'email' => 'u2@example.com']);

    TenantUser::create(['tenant_id' => $tenant->id, 'user_id' => $user1->id, 'role' => 'admin']);
    TenantUser::create(['tenant_id' => $tenant->id, 'user_id' => $user2->id, 'role' => 'member']);

    $users = TenantUser::forTenant($tenant->id)->count();

    expect($users)->toBe(2);
});

it('queries users by role', function () {
    $tenant = Tenant::create(['name' => 'Acme', 'slug' => 'acme', 'status' => 'active']);
    $user = User::create(['name' => 'Member', 'email' => 'member@example.com']);

    TenantUser::create(['tenant_id' => $tenant->id, 'user_id' => $user->id, 'role' => 'member']);

    $members = TenantUser::withRole('member')->count();
    $admins = TenantUser::withRole('admin')->count();

    expect($members)->toBe(1)
        ->and($admins)->toBe(0);
});
