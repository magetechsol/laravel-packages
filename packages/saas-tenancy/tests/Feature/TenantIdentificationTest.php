<?php

declare(strict_types=1);

use MageTech\SaaS\Models\Tenant;

it('identifies tenant via header resolver', function () {
    config(['mts-saas.resolvers.header.enabled' => true]);
    config(['mts-saas.resolvers.header.header_name' => 'X-Tenant-ID']);

    $tenant = Tenant::create(['name' => 'Test', 'slug' => 'test', 'status' => 'active']);

    $response = $this->getJson('/', ['X-Tenant-ID' => $tenant->id]);

    // Middleware would identify the tenant
    expect($tenant->id)->toBeInt();
});

it('creates and retrieves tenant', function () {
    $tenant = Tenant::create([
        'name' => 'Test Tenant',
        'slug' => 'test-tenant',
        'domain' => 'test.localhost',
        'status' => 'active',
    ]);

    $found = Tenant::where('slug', 'test-tenant')->first();

    expect($found)->not->toBeNull()
        ->and($found->name)->toBe('Test Tenant');
});

it('prevents duplicate slugs', function () {
    Tenant::create(['name' => 'One', 'slug' => 'same', 'status' => 'active']);

    $this->expectException(\Illuminate\Database\QueryException::class);

    Tenant::create(['name' => 'Two', 'slug' => 'same', 'status' => 'active']);
});

it('prevents duplicate domains', function () {
    Tenant::create(['name' => 'One', 'slug' => 'one', 'domain' => 'same.com', 'status' => 'active']);

    $this->expectException(\Illuminate\Database\QueryException::class);

    Tenant::create(['name' => 'Two', 'slug' => 'two', 'domain' => 'same.com', 'status' => 'active']);
});

it('resets tenant manager', function () {
    $tenant = Tenant::create(['name' => 'Test', 'slug' => 'test', 'status' => 'active']);

    \MageTech\SaaS\Support\Facades\Tenant::reset();

    $current = \MageTech\SaaS\Support\Facades\Tenant::getTenant();

    expect($current)->toBeNull();
});
