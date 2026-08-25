<?php

declare(strict_types=1);

use MageTech\SaaS\Models\Tenant;
use MageTech\SaaS\Support\Facades\Tenant as TenantFacade;

it('deletes tenant data in shared strategy', function () {
    config(['mts-saas.strategy' => 'shared']);

    $tenant = Tenant::create(['name' => 'Delete', 'slug' => 'delete', 'status' => 'active']);

    TenantFacade::delete($tenant);

    expect(Tenant::find($tenant->id))->toBeNull()
        ->and(Tenant::withTrashed()->find($tenant->id))->not->toBeNull();
});

it('switches strategy via config', function () {
    config(['mts-saas.strategy' => 'shared']);
    expect(config('mts-saas.strategy'))->toBe('shared');

    config(['mts-saas.strategy' => 'database']);
    expect(config('mts-saas.strategy'))->toBe('database');
});

it('creates tenant with settings', function () {
    $tenant = Tenant::create([
        'name' => 'Custom',
        'slug' => 'custom',
        'status' => 'active',
        'settings' => [
            'plan' => 'enterprise',
            'max_users' => 100,
            'features' => ['api', 'webhooks'],
        ],
    ]);

    expect($tenant->settings['plan'])->toBe('enterprise')
        ->and($tenant->settings['max_users'])->toBe(100);
});

it('handles tenant metadata', function () {
    $tenant = Tenant::create([
        'name' => 'Metadata',
        'slug' => 'metadata',
        'status' => 'active',
        'metadata' => [
            'source' => 'registration',
            'referral' => 'partner-a',
        ],
    ]);

    expect($tenant->metadata['source'])->toBe('registration');
});

it('queries tenants by domain', function () {
    Tenant::create(['name' => 'A', 'slug' => 'a', 'domain' => 'a.com', 'status' => 'active']);
    Tenant::create(['name' => 'B', 'slug' => 'b', 'domain' => 'b.com', 'status' => 'active']);

    $found = Tenant::forDomain('a.com')->first();

    expect($found->name)->toBe('A');
});
