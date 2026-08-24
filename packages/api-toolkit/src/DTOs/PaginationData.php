<?php

declare(strict_types=1);

namespace MageTech\ApiToolkit\DTOs;

use Illuminate\Pagination\LengthAwarePaginator;

readonly class PaginationData
{
    public function __construct(
        public int $currentPage,
        public int $perPage,
        public int $total,
        public int $lastPage,
        public array $links,
    ) {}

    /**
     * Create from LengthAwarePaginator.
     *
     * @param  LengthAwarePaginator  $paginator
     */
    public static function fromPaginator(LengthAwarePaginator $paginator): static
    {
        return new static(
            currentPage: $paginator->currentPage(),
            perPage: $paginator->perPage(),
            total: $paginator->total(),
            lastPage: $paginator->lastPage(),
            links: [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        );
    }

    /**
     * Convert to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'current_page' => $this->currentPage,
            'per_page' => $this->perPage,
            'total' => $this->total,
            'last_page' => $this->lastPage,
            'links' => $this->links,
        ];
    }
}
