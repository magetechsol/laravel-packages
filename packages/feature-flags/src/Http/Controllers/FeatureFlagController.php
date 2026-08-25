<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use MageTech\FeatureFlags\Http\Resources\FeatureFlagResource;
use MageTech\FeatureFlags\Models\FeatureFlag;
use MageTech\FeatureFlags\Services\FeatureFlagService;

class FeatureFlagController extends Controller
{
    public function __construct(
        protected FeatureFlagService $service,
    ) {}

    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $flags = $this->service->getAll();

        if ($request->has('environment')) {
            $flags = $flags->where('environment', $request->input('environment'));
        }

        if ($request->has('type')) {
            $flags = $flags->where('type', $request->input('type'));
        }

        return FeatureFlagResource::collection($flags);
    }

    public function show(string $key): FeatureFlagResource
    {
        $flag = $this->findFlag($key);

        return new FeatureFlagResource($flag);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255|regex:/^[a-z0-9._-]+$/',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:boolean,percentage,variant,config',
            'enabled' => 'boolean',
            'environment' => 'nullable|string|max:255',
            'rollout_percentage' => 'integer|min:0|max:100',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'default_variant' => 'nullable|string|max:255',
            'metadata' => 'nullable|array',
        ]);

        $validated['created_by'] = $request->user()?->id;

        $flag = $this->service->create($validated);

        return (new FeatureFlagResource($flag))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, string $key): FeatureFlagResource
    {
        $flag = $this->findFlag($key);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|in:boolean,percentage,variant,config',
            'enabled' => 'boolean',
            'environment' => 'nullable|string|max:255',
            'rollout_percentage' => 'integer|min:0|max:100',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
            'default_variant' => 'nullable|string|max:255',
            'metadata' => 'nullable|array',
        ]);

        $validated['updated_by'] = $request->user()?->id;

        $flag = $this->service->update($flag, $validated);

        return new FeatureFlagResource($flag);
    }

    public function destroy(string $key): JsonResponse
    {
        $flag = $this->findFlag($key);

        $this->service->delete($flag);

        return response()->json(['message' => 'Feature flag deleted.'], 200);
    }

    public function enable(string $key): FeatureFlagResource
    {
        $flag = $this->service->enable($key);

        return new FeatureFlagResource($flag);
    }

    public function disable(string $key): FeatureFlagResource
    {
        $flag = $this->service->disable($key);

        return new FeatureFlagResource($flag);
    }

    public function evaluate(Request $request, string $key): JsonResponse
    {
        $flag = $this->findFlag($key);

        $subject = $this->resolveSubject($request);

        $result = [
            'key' => $flag->key,
            'enabled' => $this->service->for($subject)->enabled($key),
            'variant' => $this->service->for($subject)->variant($key),
            'value' => $this->service->for($subject)->value($key),
        ];

        return response()->json($result);
    }

    protected function findFlag(string $key): FeatureFlag
    {
        $environment = app(\MageTech\FeatureFlags\Support\EnvironmentResolver::class)->resolve();

        $flag = $this->service->getAll()
            ->where('key', $key)
            ->where(function ($q) use ($environment) {
                $q->where('environment', $environment)
                    ->orWhereNull('environment');
            })
            ->first();

        if ($flag === null) {
            abort(404, "Feature flag [{$key}] not found.");
        }

        return $flag;
    }

    protected function resolveSubject(Request $request): mixed
    {
        if ($request->has('user_id')) {
            return $request->input('user_id');
        }

        return $request->user();
    }
}
