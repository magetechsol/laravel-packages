<?php

declare(strict_types=1);

namespace MageTech\Audit\Support;

use Illuminate\Database\Eloquent\Model;
use MageTech\Audit\Models\Audit;

/**
 * @method \Illuminate\Database\Eloquent\Relations\MorphMany audits()
 */
trait AuditableTrait
{
    public static function bootAuditableTrait(): void
    {
        static::created(function (Model $model) {
            if (!$model->isAuditEnabled()) {
                return;
            }

            $model->recordAuditEvent('created', null, $model->toArray());
        });

        static::updated(function (Model $model) {
            if (!$model->isAuditEnabled()) {
                return;
            }

            $dirty = $model->getDirty();
            $original = $model->getOriginal();

            $oldValues = [];
            $newValues = [];

            foreach (array_keys($dirty) as $attribute) {
                if ($model->isAuditExcluded($attribute)) {
                    continue;
                }

                $oldValues[$attribute] = $original[$attribute] ?? null;
                $newValues[$attribute] = $dirty[$attribute];
            }

            if (empty($oldValues)) {
                return;
            }

            $model->recordAuditEvent('updated', $oldValues, $newValues);
        });

        static::deleted(function (Model $model) {
            if (!$model->isAuditEnabled()) {
                return;
            }

            $model->recordAuditEvent('deleted', $model->getOriginal(), null);
        });

        static::restored(function (Model $model) {
            if (!$model->isAuditEnabled()) {
                return;
            }

            $model->recordAuditEvent('restored', null, $model->toArray());
        });
    }

    public function recordAuditEvent(string $event, ?array $oldValues, ?array $newValues): void
    {
        $changedValues = null;

        if ($oldValues !== null && $newValues !== null) {
            $changedValues = $this->computeChangedValues($oldValues, $newValues);
        }

        $metadata = array_merge(
            $this->getAuditMetadata(),
            $this->resolveRequestContext()
        );

        $tags = $this->getAuditTags();

        $data = AuditData::fromArray([
            'event' => $event,
            'auditable_type' => get_class($this),
            'auditable_id' => $this->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'changed_values' => $changedValues,
            'metadata' => $metadata,
            'tags' => $tags,
        ]);

        app(\MageTech\Audit\Contracts\AuditStore::class)->record($data);
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

        if (config('audit.request.auth_guard', false)) {
            $context['auth_guard'] = $request->user() ? $request->user()->getAuthIdentifierName() : null;
        }

        return array_filter($context, fn ($v) => $v !== null);
    }

    public function isAuditExcluded(string $attribute): bool
    {
        $globalExclude = config('audit.exclude', []);
        $modelExclude = $this->getAuditExcludeAttributes();

        return in_array($attribute, array_merge($globalExclude, $modelExclude), true);
    }

    public function isAuditMasked(string $attribute): bool
    {
        $maskedFields = config('audit.masking.fields', []);
        $modelMasked = $this->getAuditMaskedAttributes();

        return in_array($attribute, array_merge($maskedFields, $modelMasked), true);
    }

    public function getAuditExcludeAttributes(): array
    {
        return property_exists($this, 'auditExclude') ? $this->auditExclude : [];
    }

    public function getAuditMaskedAttributes(): array
    {
        return property_exists($this, 'auditMasked') ? $this->auditMasked : [];
    }

    public function getAuditMetadata(): array
    {
        return [];
    }

    public function getAuditTags(): array
    {
        return [];
    }

    public function getAuditEventTypes(): array
    {
        return config('audit.events', []);
    }

    public function isAuditEnabled(): bool
    {
        if (property_exists($this, 'auditDisabled') && $this->auditDisabled === true) {
            return false;
        }

        return true;
    }

    public function tapAudit(array $attributes): array
    {
        return $attributes;
    }

    public function audits()
    {
        return $this->morphMany(Audit::class, 'auditable');
    }

    public function getLatestAudit()
    {
        return $this->audits()->latest()->first();
    }

    public function getFirstAudit()
    {
        return $this->audits()->oldest()->first();
    }
}
