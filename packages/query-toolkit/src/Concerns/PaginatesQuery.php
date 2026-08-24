<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use MageTech\QueryToolkit\DTOs\PaginationResult;
use MageTech\QueryToolkit\Exceptions\PaginationException;

trait PaginatesQuery
{
    protected ?int $perPage = null;

    protected ?int $page = null;

    protected bool $useCursorPagination = false;

    protected ?string $cursorColumn = 'id';

    public function perPage(int $perPage): static
    {
        $this->perPage = $perPage;

        return $this;
    }

    public function page(int $page): static
    {
        $this->page = $page;

        return $this;
    }

    public function useCursorPagination(string $column = 'id'): static
    {
        $this->useCursorPagination = true;
        $this->cursorColumn = $column;

        return $this;
    }

    protected function applyPagination(): LengthAwarePaginator|Paginator
    {
        $perPage = $this->perPage ?? $this->request->getPerPage() ?? config('mts-query.default_per_page', 15);
        $maxPerPage = config('mts-query.max_per_page', 100);

        if ($perPage > $maxPerPage) {
            if ($this->options['ignore_invalid_filters'] ?? false) {
                $perPage = $maxPerPage;
            } else {
                throw PaginationException::perPageExceedsMaximum($perPage, $maxPerPage);
            }
        }

        if ($this->useCursorPagination) {
            return $this->query->cursorPaginate($perPage, [$this->cursorColumn]);
        }

        $page = $this->page ?? $this->request->getPage() ?? 1;
        $maxPages = config('mts-query.max_pages', null);

        if ($maxPages && $page > $maxPages) {
            if ($this->options['ignore_invalid_filters'] ?? false) {
                $page = $maxPages;
            } else {
                throw PaginationException::pageExceedsMaximum($page, $maxPages);
            }
        }

        return $this->query->paginate($perPage);
    }

    public function getPaginator(): LengthAwarePaginator|Paginator
    {
        return $this->applyPagination();
    }
}
