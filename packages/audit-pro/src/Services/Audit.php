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

class Audit
{
    protected ?string $event = null;

    protected Model|null $model = null;

    protected ?ActorData $actor = null;

    protected ?string $action = null;

    protected ?string $description = null;

    protected ?array $metadata = null;

    protected ?array $tags = null;

    protected ?array $oldValues = null;

    protected ?array $newValues = null;

    protected ?array $changedValues = null;

    protected ?string $batchUuid = null;

    public function __construct(
        protected AuditStore $store,
        protected ?AuditIntegrityService $integrityService = null,
    ) {}

    public static function make(): static
    {
        $instance = new static(
            store: app(AuditStore::class),
            integrityService: app()->bound(AuditIntegrityService::class) ? app(AuditIntegrityService::class) : null,
        );

        return $instance;
    }

    public function event(string $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function on(Model $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function by(mixed $actor): static
    {
        if ($actor instanceof Model) {
            $this->actor = new ActorData(
                type: get_class($actor),
                id: $actor->getKey(),
                name: $actor->getAttribute('name') ?? $actor->getAttribute('username'),
                email: $actor->getAttribute('email'),
            );
        } elseif (is_array($actor)) {
            $this->actor = ActorData::fromArray($actor);
        } elseif ($actor instanceof ActorData) {
            $this->actor = $actor;
        }

        return $this;
    }

    public function action(string $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function description(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function metadata(array $metadata): static
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function withMetadata(array $metadata): static
    {
        return $this->metadata($metadata);
    }

    public function tags(array $tags): static
    {
        $this->tags = $tags;

        return $this;
    }

    public function withTags(array $tags): static
    {
        return $this->tags($tags);
    }

    public function values(?array $oldValues, ?array $newValues): static
    {
        $this->oldValues = $oldValues;
        $this->newValues = $newValues;

        if ($oldValues !== null && $newValues !== null) {
            $this->changedValues = $this->computeChangedValues($oldValues, $newValues);
        }

        return $this;
    }

    public function withValues(?array $oldValues, ?array $newValues, ?array $changedValues = null): static
    {
        $this->oldValues = $oldValues;
        $this->newValues = $newValues;
        $this->changedValues = $changedValues;

        return $this;
    }

    public function forModel(Model $model): static
    {
        return $this->on($model);
    }

    public function batch(string $batchUuid): static
    {
        $this->batchUuid = $batchUuid;

        return $this;
    }

    public function beginBatch(): string
    {
        $uuid = (string) Str::uuid();
        $this->batchUuid = $uuid;

        return $uuid;
    }

    public function save(): void
    {
        $data = $this->buildData();

        if ($this->integrityService && config('audit.integrity.enabled', false)) {
            $previousHash = $this->getLastRecordHash();
            $hash = $this->integrityService->generateHash($data->toArray(), $previousHash);
            $data = $data->withHash($hash, $previousHash);
        }

        $this->store->record($data);
    }

    public function record(): static
    {
        return $this;
    }

    protected function buildData(): AuditData
    {
        $metadata = $this->metadata ?? [];

        if ($this->model) {
            $metadata = array_merge($metadata, $this->model->getAuditMetadata());
        }

        $tags = $this->tags ?? [];

        if ($this->model) {
            $tags = array_merge($tags, $this->model->getAuditTags());
        }

        return AuditData::fromArray([
            'event' => $this->event ?? 'custom',
            'auditable_type' => $this->model ? get_class($this->model) : null,
            'auditable_id' => $this->model?->getKey(),
            'actor_type' => $this->actor?->type,
            'actor_id' => $this->actor?->id,
            'actor_name' => $this->actor?->name,
            'actor_email' => $this->actor?->email,
            'action' => $this->action,
            'description' => $this->description,
            'old_values' => $this->oldValues,
            'new_values' => $this->newValues,
            'changed_values' => $this->changedValues,
            'metadata' => $metadata,
            'tags' => $tags,
            'batch_uuid' => $this->batchUuid,
        ]);
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

    protected function getLastRecordHash(): ?string
    {
        if (!config('audit.integrity.enabled', false)) {
            return null;
        }

        $model = \MageTech\Audit\Models\Audit::query()
            ->where('tenant_id', $this->resolveTenantId())
            ->latest()
            ->first();

        return $model?->record_hash;
    }

    protected function resolveTenantId(): ?int
    {
        if (!config('audit.tenancy.enabled', false)) {
            return null;
        }

        $resolver = config('audit.tenancy.resolver');

        if ($resolver && class_exists($resolver)) {
            return app($resolver)->resolve();
        }

        return null;
    }
}
