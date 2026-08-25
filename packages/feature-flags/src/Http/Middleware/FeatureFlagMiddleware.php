<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use MageTech\FeatureFlags\Services\FeatureFlagService;
use Symfony\Component\HttpFoundation\Response;

class FeatureFlagMiddleware
{
    public function __construct(
        protected FeatureFlagService $service,
    ) {}

    public function handle(Request $request, Closure $next, string $flagKey): Response
    {
        $subject = $request->user();

        if (! $this->service->for($subject)->enabled($flagKey)) {
            return $this->handleDisabled($request, $flagKey);
        }

        return $next($request);
    }

    protected function handleDisabled(Request $request, string $flagKey): Response
    {
        $response = config('mts-feature-flags.middleware.response', '404');

        return match ($response) {
            '404' => response()->view('errors.404', [
                'message' => "Feature [{$flagKey}] is not available.",
            ], 404),
            '403' => response()->json([
                'message' => config('mts-feature-flags.middleware.json_message', 'Feature not available.'),
            ], 403),
            '404_json' => response()->json([
                'message' => config('mts-feature-flags.middleware.json_message', 'Feature not available.'),
            ], config('mts-feature-flags.middleware.json_status', 404)),
            'redirect' => redirect()->to(
                config('mts-feature-flags.middleware.redirect_url', '/')
            ),
            default => response()->json([
                'message' => config('mts-feature-flags.middleware.json_message', 'Feature not available.'),
            ], config('mts-feature-flags.middleware.json_status', 404)),
        };
    }
}
