<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyWebhookIp
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowedIps = config('mts-webhooks.security.ip_restrictions', []);

        if (empty($allowedIps)) {
            return $next($request);
        }

        $clientIp = $request->ip();

        if (! in_array($clientIp, $allowedIps, true)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
