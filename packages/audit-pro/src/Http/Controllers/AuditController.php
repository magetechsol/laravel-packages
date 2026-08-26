<?php

declare(strict_types=1);

namespace MageTech\Audit\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use MageTech\Audit\Models\Audit;
use MageTech\Audit\Services\Auditor;

class AuditController extends Controller
{
    public function __construct(
        protected Auditor $auditor,
    ) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('audit.view');

        $query = $this->auditor->query();

        if ($event = $request->input('event')) {
            $query->whereEvent($event);
        }

        if ($actorType = $request->input('actor_type')) {
            $query->whereActor($actorType, $request->input('actor_id'));
        }

        if ($modelType = $request->input('model_type')) {
            $query->whereModel($modelType, $request->input('model_id'));
        }

        if ($tenantId = $request->input('tenant_id')) {
            $query->whereTenant($tenantId);
        }

        if ($from = $request->input('from')) {
            $to = $request->input('to', now()->toDateTimeString());
            $query->whereDateRange($from, $to);
        }

        if ($ip = $request->input('ip')) {
            $query->whereIp($ip);
        }

        if ($requestId = $request->input('request_id')) {
            $query->whereRequestId($requestId);
        }

        if ($batch = $request->input('batch')) {
            $query->whereBatch($batch);
        }

        if ($tag = $request->input('tag')) {
            $query->whereTag($tag);
        }

        if ($action = $request->input('action')) {
            $query->whereAction($action);
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $results = $query->latest()->paginate($perPage);

        return response()->json([
            'data' => $results['data'],
            'meta' => [
                'total' => $results['total'],
                'per_page' => $results['per_page'],
                'current_page' => $results['current_page'],
                'last_page' => $results['last_page'],
            ],
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        Gate::authorize('audit.view_details');

        $audit = Audit::where('uuid', $uuid)->firstOrFail();

        return response()->json([
            'data' => $audit->toArray(),
        ]);
    }

    public function changes(string $uuid): JsonResponse
    {
        Gate::authorize('audit.view_details');

        $audit = Audit::where('uuid', $uuid)->firstOrFail();

        return response()->json([
            'data' => [
                'uuid' => $audit->uuid,
                'event' => $audit->event,
                'old_values' => $audit->old_values,
                'new_values' => $audit->new_values,
                'changed_values' => $audit->changed_values,
            ],
        ]);
    }

    public function auditable(string $type, int|string $id): JsonResponse
    {
        Gate::authorize('audit.view');

        $audits = Audit::query()
            ->where('auditable_type', $type)
            ->where('auditable_id', $id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json([
            'data' => $audits['data'],
            'meta' => [
                'total' => $audits['total'],
                'per_page' => $audits['per_page'],
                'current_page' => $audits['current_page'],
                'last_page' => $audits['last_page'],
            ],
        ]);
    }

    public function actor(int|string $id): JsonResponse
    {
        Gate::authorize('audit.view');

        $audits = Audit::query()
            ->where('actor_id', $id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json([
            'data' => $audits['data'],
            'meta' => [
                'total' => $audits['total'],
                'per_page' => $audits['per_page'],
                'current_page' => $audits['current_page'],
                'last_page' => $audits['last_page'],
            ],
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        Gate::authorize('audit.view');

        $query = Audit::query();

        if ($tenantId = $request->input('tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }

        $totalEvents = (clone $query)->count();
        $eventsToday = (clone $query)->whereDate('created_at', today())->count();
        $activeActors = (clone $query)->distinct('actor_type', 'actor_id')->count();

        $topEvents = (clone $query)
            ->select('event', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('event')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $recentActivity = (clone $query)
            ->latest()
            ->limit(10)
            ->get();

        return response()->json([
            'data' => [
                'total_events' => $totalEvents,
                'events_today' => $eventsToday,
                'active_actors' => $activeActors,
                'top_events' => $topEvents,
                'recent_activity' => $recentActivity,
            ],
        ]);
    }
}
