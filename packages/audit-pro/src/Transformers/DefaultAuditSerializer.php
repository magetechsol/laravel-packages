<?php

declare(strict_types=1);

namespace MageTech\Audit\Transformers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use MageTech\Audit\Contracts\AuditSerializer;

class DefaultAuditSerializer implements AuditSerializer
{
    protected int $maxDepth;

    protected int $maxStringLength;

    public function __construct()
    {
        $this->maxDepth = config('audit.serialization.max_depth', 10);
        $this->maxStringLength = config('audit.serialization.max_string_length', 65535);
    }

    public function serialize(mixed $value): mixed
    {
        return $this->processValue($value, 0);
    }

    public function unserialize(mixed $value): mixed
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $value;
    }

    public function serializeModel($model): array
    {
        if (!$model instanceof Model) {
            return (array) $model;
        }

        $attributes = $model->getAttributes();
        $serialized = [];

        foreach ($attributes as $key => $value) {
            $serialized[$key] = $this->serialize($value);
        }

        return $serialized;
    }

    protected function processValue(mixed $value, int $depth): mixed
    {
        if ($depth >= $this->maxDepth) {
            return $this->truncateString($value);
        }

        if ($value === null || is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_string($value)) {
            return $this->truncateString($value);
        }

        if ($value instanceof Model) {
            return $this->serializeModel($value);
        }

        if ($value instanceof Collection) {
            return $value->map(fn ($item) => $this->processValue($item, $depth + 1))->toArray();
        }

        if (is_array($value)) {
            return array_map(fn ($item) => $this->processValue($item, $depth + 1), $value);
        }

        if (is_object($value)) {
            if (method_exists($value, 'toArray')) {
                return $this->processValue($value->toArray(), $depth + 1);
            }

            return $this->truncateString((string) $value);
        }

        return $this->truncateString($value);
    }

    protected function truncateString(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        if (mb_strlen($value) > $this->maxStringLength) {
            return mb_substr($value, 0, $this->maxStringLength) . '...[truncated]';
        }

        return $value;
    }
}
