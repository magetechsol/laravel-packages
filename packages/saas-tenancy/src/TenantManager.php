<?php

declare(strict_types=1);

namespace MageTech\SaaS;

use Illuminate\Config\Repository;
use Illuminate\Support\Arr;
use MageTech\SaaS\Contracts\DatabaseStrategyContract;
use MageTech\SaaS\Contracts\TenantResolverContract;
use MageTech\SaaS\Events\TenantActivated;
use MageTech\SaaS\Events\TenantCreated;
use MageTech\SaaS\Events\TenantDeleted;
use MageTech\SaaS\Events\TenantIdentified;
use MageTech\SaaS\Events\TenantSuspended;
use MageTech\SaaS\Exceptions\TenantNotFoundException;
use MageTech\SaaS\Exceptions\TenantSuspendedException;
use MageTech\SaaS\Models\Tenant;

class TenantManager
{
    protected ?Tenant $current = null;

    protected bool $identified = false;

    public function __construct(
        protected Repository $config,
        protected TenantResolverContract $resolver,
        protected DatabaseStrategyContract $strategy,
    ) {}

    public function identify(): ?Tenant
    {
        if ($this->identified) {
            return $this->current;
        }

        $tenant = $this->resolver->resolve();

        if ($tenant) {
            $this->setTenant($tenant);
            event(new TenantIdentified($tenant));
        }

        $this->identified = true;

        return $this->current;
    }

    public function setTenant(Tenant $tenant): void
    {
        $this->current = $tenant;
        $this->identified = true;

        $this->strategy->setTenant($tenant);
    }

    public function getTenant(): ?Tenant
    {
        if (! $this->identified) {
            $this->identify();
        }

        return $this->current;
    }

    public function getTenantId(): ?string
    {
        return $this->getTenant()?->getKey();
    }

    public function getTenantKey(): ?string
    {
        return $this->getTenant()?->{$this->config->get('mts-saas.key_column', 'tenant_id')};
    }

    public function isActive(): bool
    {
        $tenant = $this->getTenant();

        return $tenant !== null && $tenant->isActive();
    }

    public function isSuspended(): bool
    {
        $tenant = $this->getTenant();

        return $tenant !== null && $tenant->isSuspended();
    }

    public function getStrategy(): DatabaseStrategyContract
    {
        return $this->strategy;
    }

    public function create(array $data): Tenant
    {
        $tenant = $this->strategy->createTenant($data);

        event(new TenantCreated($tenant));

        return $tenant;
    }

    public function activate(Tenant $tenant): void
    {
        $tenant->activate();

        event(new TenantActivated($tenant));
    }

    public function suspend(Tenant $tenant, ?string $reason = null): void
    {
        $tenant->suspend($reason);

        event(new TenantSuspended($tenant));
    }

    public function delete(Tenant $tenant): void
    {
        event(new TenantDeleted($tenant));

        $this->strategy->deleteTenant($tenant);
    }

    public function getDatabaseName(Tenant $tenant): string
    {
        return $this->strategy->getDatabaseName($tenant);
    }

    public function migrate(Tenant $tenant): void
    {
        $this->strategy->migrate($tenant);
    }

    public function migrateAll(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $this->migrate($tenant);
        }
    }

    public function reset(): void
    {
        $this->current = null;
        $this->identified = false;
        $this->strategy->reset();
    }

    public function __call(string $method, array $parameters)
    {
        return $this->strategy->{$method}(...$parameters);
    }
}
