<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\DTOs;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;

readonly class PaginationResult
{
    public function __construct(
        public Paginator $paginator,
        public int $currentPage,
        public int $perPage,
        public ?int $total = null,
        public ?int $lastPage = null,
    ) {}

    public static function fromPaginator(LengthAwarePaginator $paginator): static
    {
        return new static(
            paginator: $paginator,
            currentPage: $paginator->currentPage(),
            perPage: $paginator->perPage(),
            total: $paginator->total(),
            lastPage: $paginator->lastPage(),
        );
    }

    public static function fromSimplePaginator(Paginator $paginator): static
    {
        return new static(
            paginator: $paginator,
            currentPage: $paginator->currentPage(),
            perPage: $paginator->perPage(),
        );
    }

    public function toArray(): array
    {
        return [
            'data' => $this->paginator->items(),
            'meta' => [
                'current_page' => $this->currentPage,
                'per_page' => $this->perPage,
                'total' => $this->total,
                'last_page' => $this->lastPage,
            ],
            'links' => [
                'first' => $this->paginator->url(1),
                'last' => $this->lastPage ? $this->paginator->url($this->lastPage) : null,
                'prev' => $this->paginator->previousPageUrl(),
                'next' => $this->paginator->nextPageUrl(),
            ],
        ];
    }
}
