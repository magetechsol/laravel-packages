<?php

declare(strict_types=1);

namespace MageTech\SaaS;

use Closure;
use Illuminate\Config\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MageTech\SaaS\Contracts\TenantResolverContract;
use MageTech\SaaS\Models\Tenant;

class TenantResolver implements TenantResolverContract
{
    protected array $resolvers = [];

    protected ?Tenant $resolved = null;

    public function __construct(
        protected Repository $config,
        protected Request $request,
    ) {
        $this->registerDefaultResolvers();
    }

    public function resolve(): ?Tenant
    {
        if ($this->resolved) {
            return $this->resolved;
        }

        if ($this->isCentralDomain()) {
            return null;
        }

        foreach ($this->getActiveResolvers() as $name => $resolver) {
            $tenant = $resolver($this->request);

            if ($tenant instanceof Tenant && $tenant->exists) {
                $this->resolved = $tenant;

                return $tenant;
            }
        }

        return null;
    }

    public function register(string $name, Closure $resolver): void
    {
        $this->resolvers[$name] = $resolver;
    }

    public function getResolved(): ?Tenant
    {
        return $this->resolved;
    }

    public function setResolved(Tenant $tenant): void
    {
        $this->resolved = $tenant;
    }

    public function flush(): void
    {
        $this->resolved = null;
    }

    protected function registerDefaultResolvers(): void
    {
        $this->register('subdomain', function (Request $request) {
            if (! $this->config->get('mts-saas.resolvers.subdomain.enabled', true)) {
                return null;
            }

            $rootDomain = $this->config->get('mts-saas.resolvers.subdomain.root_domain', 'example.com');
            $host = $request->getHost();

            if ($host === $rootDomain || ! str_ends_with($host, ".{$rootDomain}")) {
                return null;
            }

            $subdomain = explode('.', $host)[0];

            return $this->findTenantBy('domain', $subdomain);
        });

        $this->register('domain', function (Request $request) {
            if (! $this->config->get('mts-saas.resolvers.domain.enabled', false)) {
                return null;
            }

            $mapping = $this->config->get('mts-saas.resolvers.domain.mapping', []);
            $host = $request->getHost();

            $tenantId = $mapping[$host] ?? null;

            if ($tenantId) {
                return Tenant::find($tenantId);
            }

            return $this->findTenantBy('domain', $host);
        });

        $this->register('path', function (Request $request) {
            if (! $this->config->get('mts-saas.resolvers.path.enabled', false)) {
                return null;
            }

            $prefix = $this->config->get('mts-saas.resolvers.path.prefix', 'tenant');
            $path = $request->path();

            if (str_starts_with($path, "{$prefix}/")) {
                $slug = explode('/', $path)[1] ?? null;

                if ($slug) {
                    return $this->findTenantBy('slug', $slug);
                }
            }

            return null;
        });

        $this->register('header', function (Request $request) {
            if (! $this->config->get('mts-saas.resolvers.header.enabled', false)) {
                return null;
            }

            $headerName = $this->config->get('mts-saas.resolvers.header.header_name', 'X-Tenant-ID');
            $tenantId = $request->header($headerName);

            if ($tenantId) {
                return Tenant::find($tenantId);
            }

            return null;
        });

        $this->register('session', function (Request $request) {
            if (! $this->config->get('mts-saas.resolvers.session.enabled', false)) {
                return null;
            }

            $key = $this->config->get('mts-saas.resolvers.session.key', 'tenant_id');
            $tenantId = session($key);

            if ($tenantId) {
                return Tenant::find($tenantId);
            }

            return null;
        });

        $this->register('cookie', function (Request $request) {
            if (! $this->config->get('mts-saas.resolvers.cookie.enabled', false)) {
                return null;
            }

            $name = $this->config->get('mts-saas.resolvers.cookie.name', 'tenant_id');
            $tenantId = $request->cookie($name);

            if ($tenantId) {
                return Tenant::find($tenantId);
            }

            return null;
        });
    }

    protected function getActiveResolvers(): array
    {
        $resolvers = [];
        $resolverConfig = $this->config->get('mts-saas.resolvers', []);

        foreach ($resolverConfig as $name => $settings) {
            if ($name === 'default') {
                continue;
            }

            if (is_array($settings) && ($settings['enabled'] ?? false) && isset($this->resolvers[$name])) {
                $resolvers[$name] = $this->resolvers[$name];
            }
        }

        $default = $resolverConfig['default'] ?? 'subdomain';

        if (isset($resolvers[$default])) {
            $resolver = $resolvers[$default];
            unset($resolvers[$default]);
            $resolvers = array_merge([$default => $resolver], $resolvers);
        }

        return $resolvers;
    }

    protected function findTenantBy(string $column, mixed $value): ?Tenant
    {
        return Tenant::where($column, $value)
            ->where('status', 'active')
            ->first();
    }

    protected function isCentralDomain(): bool
    {
        $centralDomains = $this->config->get('mts-saas.central_domains', ['localhost']);
        $host = $this->request->getHost();

        return in_array($host, $centralDomains, true);
    }
}
