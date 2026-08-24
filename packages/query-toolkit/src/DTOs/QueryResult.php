<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\DTOs;

use Illuminate\Database\Eloquent\Collection;

readonly class QueryResult
{
    public function __construct(
        public Collection $items,
        public array $appliedFilters = [],
        public array $appliedSorts = [],
        public array $appliedIncludes = [],
        public ?string $searchTerm = null,
        public ?PaginationResult $pagination = null,
    ) {}

    public function toArray(): array
    {
        $result = [
            'data' => $this->items->toArray(),
            'meta' => [
                'applied_filters' => $this->appliedFilters,
                'applied_sorts' => $this->appliedSorts,
                'applied_includes' => $this->appliedIncludes,
                'search_term' => $this->searchTerm,
            ],
        ];

        if ($this->pagination) {
            $result['meta']['pagination'] = $this->pagination->toArray();
        }

        return $result;
    }
}
