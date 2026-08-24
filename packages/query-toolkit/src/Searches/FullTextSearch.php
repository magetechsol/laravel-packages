<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Searches;

use Illuminate\Database\Eloquent\Builder;
use MageTech\QueryToolkit\Contracts\SearchInterface;

class FullTextSearch implements SearchInterface
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
        $driver = $query->getModel()->getConnection()->getDriverName();

        if ($driver === 'mysql') {
            return $this->applyMySqlFullText($query, $term);
        }

        if ($driver === 'pgsql') {
            return $this->applyPostgresFullText($query, $term);
        }

        return $this->applyLikeFallback($query, $term);
    }

    private function applyMySqlFullText(Builder $query, string $term): Builder
    {
        $columns = implode(', ', $this->fields);
        $query->whereRaw("MATCH ({$columns}) AGAINST (? IN BOOLEAN MODE)", [$term]);

        return $query;
    }

    private function applyPostgresFullText(Builder $query, string $term): Builder
    {
        $tsQuery = $this->buildTsQuery($term);

        return $query->whereRaw(
            "to_tsvector('english', " . implode(" || ' ' || ", $this->fields) . ") @@ to_tsquery('english', ?)",
            [$tsQuery]
        );
    }

    private function applyLikeFallback(Builder $query, string $term): Builder
    {
        $query->where(function (Builder $q) use ($term) {
            foreach ($this->fields as $field) {
                $q->orWhere($field, 'LIKE', '%' . $term . '%');
            }
        });

        return $query;
    }

    private function buildTsQuery(string $term): string
    {
        $words = explode(' ', $term);

        return implode(' & ', array_map(function (string $word) {
            return preg_replace('/[^\w]/', '', $word) . ':*';
        }, $words));
    }
}
