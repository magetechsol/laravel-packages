<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Mappers;

final class ColumnMapper
{
    private array $mapping = [];

    private array $defaults = [];

    private array $skipColumns = [];

    public function __construct(
        array $mapping = [],
        array $defaults = [],
        array $skipColumns = [],
    ) {
        $this->mapping = $mapping;
        $this->defaults = $defaults;
        $this->skipColumns = $skipColumns;
    }

    public function setMapping(array $mapping): static
    {
        $this->mapping = $mapping;

        return $this;
    }

    public function setDefaults(array $defaults): static
    {
        $this->defaults = $defaults;

        return $this;
    }

    public function setSkipColumns(array $columns): static
    {
        $this->skipColumns = $columns;

        return $this;
    }

    /**
     * Map a raw row using the configured column mapping.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function map(array $row): array
    {
        if ($this->mapping === []) {
            return $this->applyDefaults($row);
        }

        $mapped = [];

        foreach ($this->mapping as $source => $target) {
            if (in_array($source, $this->skipColumns, true)) {
                continue;
            }

            if (str_contains($target, '.')) {
                $this->setNestedValue($mapped, $target, $row[$source] ?? null);
            } else {
                $mapped[$target] = $row[$source] ?? null;
            }
        }

        return $this->applyDefaults($mapped);
    }

    /**
     * Map multiple rows.
     *
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    public function mapRows(array $rows): array
    {
        return array_map([$this, 'map'], $rows);
    }

    private function applyDefaults(array $row): array
    {
        foreach ($this->defaults as $key => $value) {
            if (! array_key_exists($key, $row) || $row[$key] === null) {
                $row[$key] = $value;
            }
        }

        return $row;
    }

    private function setNestedValue(array &$array, string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $current = &$array;

        foreach (array_slice($keys, 0, -1) as $keyPart) {
            if (! isset($current[$keyPart]) || ! is_array($current[$keyPart])) {
                $current[$keyPart] = [];
            }

            $current = &$current[$keyPart];
        }

        $current[end($keys)] = $value;
    }
}
