<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Transformers;

final class TypeCaster
{
    private const TYPE_MAP = [
        'string' => 'castString',
        'int' => 'castInt',
        'integer' => 'castInt',
        'float' => 'castFloat',
        'double' => 'castFloat',
        'bool' => 'castBool',
        'boolean' => 'castBool',
        'date' => 'castDate',
        'datetime' => 'castDateTime',
        'json' => 'castJson',
        'array' => 'castArray',
    ];

    /**
     * @param  array<string, string>  $types  key => type
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function cast(array $row, array $types): array
    {
        foreach ($types as $column => $type) {
            if (! array_key_exists($column, $row)) {
                continue;
            }

            $row[$column] = $this->castValue($row[$column], $type);
        }

        return $row;
    }

    public function castValue(mixed $value, string $type): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        $method = self::TYPE_MAP[$type] ?? 'castString';

        return $this->{$method}($value);
    }

    private function castString(mixed $value): string
    {
        return (string) $value;
    }

    private function castInt(mixed $value): int
    {
        return (int) $value;
    }

    private function castFloat(mixed $value): float
    {
        return (float) filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    private function castBool(mixed $value): bool
    {
        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }

    private function castDate(mixed $value): ?string
    {
        $timestamp = strtotime((string) $value);

        return $timestamp !== false ? date('Y-m-d', $timestamp) : null;
    }

    private function castDateTime(mixed $value): ?string
    {
        $timestamp = strtotime((string) $value);

        return $timestamp !== false ? date('Y-m-d H:i:s', $timestamp) : null;
    }

    private function castJson(mixed $value): mixed
    {
        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }

    private function castArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        return explode(',', (string) $value);
    }
}
