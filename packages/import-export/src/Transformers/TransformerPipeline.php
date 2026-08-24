<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Transformers;

/**
 * @phpstan-type TransformCallback callable(array<string, mixed>): array<string, mixed>
 */
final class TransformerPipeline
{
    /**
     * @var list<TransformCallback>
     */
    private array $transforms = [];

    private ?TypeCaster $typeCaster = null;

    private ?Sanitizer $sanitizer = null;

    /**
     * @param  list<TransformCallback>  $transforms
     */
    public function __construct(
        array $transforms = [],
    ) {
        $this->transforms = $transforms;
    }

    /**
     * Add a transform callback to the pipeline.
     *
     * @param  TransformCallback  $callback
     */
    public function add(callable $callback): static
    {
        $this->transforms[] = $callback;

        return $this;
    }

    /**
     * Add multiple transforms.
     *
     * @param  list<TransformCallback>  $callbacks
     */
    public function addMany(array $callbacks): static
    {
        foreach ($callbacks as $callback) {
            $this->add($callback);
        }

        return $this;
    }

    public function withTypeCaster(array $types): static
    {
        $this->typeCaster = new TypeCaster;

        $this->transforms[] = fn (array $row) => $this->typeCaster->cast($row, $types);

        return $this;
    }

    public function withSanitizer(?Sanitizer $sanitizer = null): static
    {
        $this->sanitizer = $sanitizer ?? new Sanitizer;

        $this->transforms[] = fn (array $row) => $this->sanitizer->sanitizeArray($row);

        return $this;
    }

    /**
     * Apply all transforms to a row.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function transform(array $row): array
    {
        foreach ($this->transforms as $transform) {
            $row = $transform($row);
        }

        return $row;
    }

    /**
     * Transform multiple rows.
     *
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    public function transformRows(array $rows): array
    {
        return array_map([$this, 'transform'], $rows);
    }

    public function count(): int
    {
        return count($this->transforms);
    }

    public function clear(): static
    {
        $this->transforms = [];

        return $this;
    }
}
