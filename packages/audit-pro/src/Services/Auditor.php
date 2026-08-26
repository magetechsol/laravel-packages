<?php

declare(strict_types=1);

namespace MageTech\Audit\Services;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use MageTech\Audit\Contracts\AuditIntegrityService;
use MageTech\Audit\Contracts\AuditStore;
use MageTech\Audit\Support\ActorData;
use MageTech\Audit\Support\AuditData;

class Auditor
{
    protected ?string $currentBatchUuid = null;

    public function __construct(
        protected AuditStore $store,
        protected ?AuditIntegrityService $integrityService = null,
    ) {}

    public static function make(): static
    {
        return app(static::class);
    }

    public function record(): Audit
    {
        return new Audit(
            store: $this->store,
            integrityService: $this->integrityService,
        );
    }

    public function event(string $event): Audit
    {
        return $this->record()->event($event);
    }

    public function beginBatch(): string
    {
        $this->currentBatchUuid = (string) Str::uuid();

        return $this->currentBatchUuid;
    }

    public function endBatch(): void
    {
        $this->currentBatchUuid = null;
    }

    public function getCurrentBatchUuid(): ?string
    {
        return $this->currentBatchUuid;
    }

    public function query()
    {
        return $this->store->query();
    }

    public function recordModelEvent(string $event, Model $model, ?array $oldValues = null, ?array $newValues = null): void
    {
        $changedValues = null;

        if ($oldValues !== null && $newValues !== null) {
            $changedValues = $this->computeChangedValues($oldValues, $newValues);
        }

        $metadata = $model->getAuditMetadata();

        $requestContext = $this->resolveRequestContext();
        $metadata = array_merge($metadata, $requestContext);

        $tags = $model->getAuditTags();

        $data = AuditData::fromArray([
            'event' => $event,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'changed_values' => $changedValues,
            'metadata' => $metadata,
            'tags' => $tags,
            'batch_uuid' => $this->currentBatchUuid,
        ]);

        $data = $this->applyIntegrity($data);

        $this->store->record($data);
    }

    public function recordLogin(Model $user, array $metadata = []): void
    {
        $this->recordAuthenticationEvent('login', $user, $metadata);
    }

    public function recordLogout(Model $user, array $metadata = []): void
    {
        $this->recordAuthenticationEvent('logout', $user, $metadata);
    }

    public function recordFailedLogin(string $email, array $metadata = []): void
    {
        $actor = new ActorData(
            type: 'anonymous',
            id: null,
            name: null,
            email: $email,
        );

        $this->event('failed_login')
            ->by($actor)
            ->metadata(array_merge($metadata, $this->resolveRequestContext()))
            ->save();
    }

    protected function recordAuthenticationEvent(string $event, Model $user, array $metadata): void
    {
        $actor = new ActorData(
            type: get_class($user),
            id: $user->getKey(),
            name: $user->getAttribute('name'),
            email: $user->getAttribute('email'),
        );

        $this->event($event)
            ->by($actor)
            ->metadata(array_merge($metadata, $this->resolveRequestContext()))
            ->save();
    }

    protected function computeChangedValues(array $oldValues, array $newValues): array
    {
        $changed = [];

        foreach ($newValues as $key => $newVal) {
            $oldVal = $oldValues[$key] ?? null;

            if ($oldVal !== $newVal) {
                $changed[$key] = [
                    'old' => $oldVal,
                    'new' => $newVal,
                ];
            }
        }

        return $changed;
    }

    protected function resolveRequestContext(): array
    {
        if (!config('audit.request.enabled', true)) {
            return [];
        }

        $context = [];
        $request = request();

        if (config('audit.request.ip_address', true)) {
            $context['ip_address'] = $request->ip();
        }

        if (config('audit.request.user_agent', true)) {
            $context['user_agent'] = $request->userAgent();
        }

        if (config('audit.request.url', true)) {
            $context['url'] = $request->url();
        }

        if (config('audit.request.method', true)) {
            $context['method'] = $request->method();
        }

        if (config('audit.request.route', true)) {
            $context['route'] = $request->route()?->getName();
        }

        if (config('audit.request.request_id', true)) {
            $context['request_id'] = $request->header('X-Request-Id');
        }

        if (config('audit.request.session_id', false)) {
            $context['session_id'] = $request->session()?->getId();
        }

        return array_filter($context, fn ($v) => $v !== null);
    }

    protected function applyIntegrity(AuditData $data): AuditData
    {
        if (!$this->integrityService || !config('audit.integrity.enabled', false)) {
            return $data;
        }

        $previousHash = $this->getLastRecordHash();
        $hash = $this->integrityService->generateHash($data->toArray(), $previousHash);

        return $data->withHash($hash, $previousHash);
    }

    protected function getLastRecordHash(): ?string
    {
        $model = \MageTech\Audit\Models\Audit::query()
            ->latest()
            ->first();

        return $model?->record_hash;
    }
}
