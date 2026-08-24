<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Validators;

use Illuminate\Database\Eloquent\Model;

final class DuplicateDetector
{
    private string $mode;

    private ?string $uniqueKey;

    /**
     * @var class-string<Model>|null
     */
    private ?string $modelClass = null;

    /**
     * @param  class-string<Model>|null  $modelClass
     */
    public function __construct(
        string $mode = 'ignore',
        ?string $uniqueKey = null,
        ?string $modelClass = null,
    ) {
        $this->mode = $mode;
        $this->uniqueKey = $uniqueKey;
        $this->modelClass = $modelClass;
    }

    public function setMode(string $mode): static
    {
        $this->mode = $mode;

        return $this;
    }

    public function setUniqueKey(?string $key): static
    {
        $this->uniqueKey = $key;

        return $this;
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    public function setModel(string $modelClass): static
    {
        $this->modelClass = $modelClass;

        return $this;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function getUniqueKey(): ?string
    {
        return $this->uniqueKey;
    }

    public function shouldSkip(): bool
    {
        return $this->mode === 'ignore';
    }

    public function shouldReject(): bool
    {
        return $this->mode === 'reject';
    }

    public function shouldUpsert(): bool
    {
        return $this->mode === 'upsert';
    }

    /**
     * Check if a row is a duplicate.
     *
     * @param  array<string, mixed>  $row
     */
    public function isDuplicate(array $row): bool
    {
        if ($this->uniqueKey === null || $this->modelClass === null) {
            return false;
        }

        $value = $row[$this->uniqueKey] ?? null;

        if ($value === null) {
            return false;
        }

        return $this->modelClass::where($this->uniqueKey, $value)->exists();
    }

    /**
     * Filter out duplicates from a batch of rows.
     *
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    public function filterDuplicates(array $rows): array
    {
        if ($this->uniqueKey === null || $this->modelClass === null) {
            return $rows;
        }

        $existingValues = $this->modelClass::whereIn(
            $this->uniqueKey,
            array_column($rows, $this->uniqueKey),
        )->pluck($this->uniqueKey)
            ->toArray();

        return array_filter(
            $rows,
            fn (array $row) => ! in_array($row[$this->uniqueKey] ?? null, $existingValues, true),
        );
    }
}
