<?php

declare(strict_types=1);

namespace MageTech\SaaS\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use MageTech\SaaS\Events\TenantIdentified;
use MageTech\SaaS\Exceptions\TenantNotIdentifiedException;
use MageTech\SaaS\Exceptions\TenantSuspendedException;
use MageTech\SaaS\Support\Facades\Tenant;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = Tenant::identify();

        if ($tenant === null && ! $this->isCentralRoute($request)) {
            throw TenantNotIdentifiedException::make($request);
        }

        if ($tenant && $tenant->isSuspended()) {
            throw TenantSuspendedException::make($tenant);
        }

        return $next($request);
    }

    protected function isCentralRoute(Request $request): bool
    {
        $centralDomains = config('mts-saas.central_domains', ['localhost']);
        $host = $request->getHost();

        return in_array($host, $centralDomains, true);
    }
}
