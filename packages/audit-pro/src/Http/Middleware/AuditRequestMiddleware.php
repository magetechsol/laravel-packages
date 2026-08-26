<?php

declare(strict_types=1);

namespace MageTech\Audit\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditRequestMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->merge([
            '_audit_request_id' => $request->header('X-Request-Id') ?? uniqid('req_', true),
        ]);

        $response = $next($request);

        if (config('audit.request.request_id', true)) {
            $response->headers->set('X-Audit-Request-Id', $request->input('_audit_request_id'));
        }

        return $response;
    }
}
