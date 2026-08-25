<?php

declare(strict_types=1);

namespace MageTech\SaaS\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use MageTech\SaaS\Exceptions\TenantMixingException;
use MageTech\SaaS\Support\Facades\Tenant;
use Symfony\Component\HttpFoundation\Response;

class PreventTenantMixing
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = Tenant::getTenantId();

        if ($tenantId) {
            $requestTenantId = $request->input('_tenant_id') ?? $request->header('X-Tenant-ID');

            if ($requestTenantId && $requestTenantId !== $tenantId) {
                throw TenantMixingException::detected((string) $tenantId, (string) $requestTenantId);
            }

            $request->merge(['_tenant_id' => $tenantId]);

            $response = $next($request);

            if ($response instanceof \Illuminate\Http\JsonResponse) {
                $response->headers->set('X-Tenant-ID', $tenantId);
            }

            return $response;
        }

        return $next($request);
    }
}
