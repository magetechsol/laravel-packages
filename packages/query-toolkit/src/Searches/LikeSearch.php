<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Searches;

use Illuminate\Database\Eloquent\Builder;
use MageTech\QueryToolkit\Contracts\SearchInterface;

class LikeSearch implements SearchInterface
{
    protected array $fields;
    protected array $weightedFields;

    public function __construct(array $fields, array $weightedFields = [])
    {
        $this->fields = $fields;
        $this->weightedFields = $weightedFields;
    }

    public static function make(array $fields, array $weightedFields = []): static
    {
        return new static($fields, $weightedFields);
    }

    public function apply(Builder $query, string $term): Builder
    {
        $terms = explode(' ', $term);

        $query->where(function (Builder $q) use ($terms) {
            foreach ($terms as $term) {
                $q->where(function (Builder $q) use ($term) {
                    foreach ($this->fields as $field) {
                        $q->orWhere($field, 'LIKE', '%' . $term . '%');
                    }
                });
            }
        });

        return $query;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getWeightedFields(): array
    {
        return $this->weightedFields;
    }
}
