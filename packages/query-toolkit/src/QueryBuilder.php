<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Traits\ForwardsCalls;
use MageTech\QueryToolkit\Concerns\AddsFieldsToQuery;
use MageTech\QueryToolkit\Concerns\AddsIncludesToQuery;
use MageTech\QueryToolkit\Concerns\FiltersQuery;
use MageTech\QueryToolkit\Concerns\Macroable;
use MageTech\QueryToolkit\Concerns\PaginatesQuery;
use MageTech\QueryToolkit\Concerns\PerformsSearches;
use MageTech\QueryToolkit\Concerns\SortsQuery;
use MageTech\QueryToolkit\Contracts\FilterInterface;
use MageTech\QueryToolkit\Contracts\IncludeInterface;
use MageTech\QueryToolkit\Contracts\SearchInterface;
use MageTech\QueryToolkit\Contracts\SortInterface;
use MageTech\QueryToolkit\DTOs\PaginationResult;
use MageTech\QueryToolkit\DTOs\QueryResult;

/**
 * QueryBuilder for Laravel APIs.
 *
 * @method static \MageTech\QueryToolkit\QueryBuilder for(string $modelClass, ?Request $request = null)
 * @method static \MageTech\QueryToolkit\QueryBuilder fromQuery(Builder $query, ?Request $request = null)
 */
class QueryBuilder
{
    use FiltersQuery;
    use SortsQuery;
    use AddsIncludesToQuery;
    use AddsFieldsToQuery;
    use PerformsSearches;
    use PaginatesQuery;
    use Macroable;
    use ForwardsCalls;

    protected Builder $query;

    protected QueryBuilderRequest $request;

    protected string $modelClass;

    protected array $options = [
        'ignore_invalid_filters' => false,
        'allowed_relations' => null,
    ];

    public function __construct(Builder $query, ?Request $request = null, array $options = [])
    {
        $this->query = $query;
        $this->modelClass = get_class($this->getModel());
        $this->request = QueryBuilderRequest::fromRequest($request ?? request());
        $this->options = array_merge($this->options, $options);
    }

    public static function for(string $modelClass, ?Request $request = null, array $options = []): static
    {
        $model = new $modelClass;

        return new static($model->newQuery(), $request, $options);
    }

    public static function fromQuery(Builder $query, ?Request $request = null, array $options = []): static
    {
        return new static($query, $request, $options);
    }

    public function mts(): static
    {
        return $this;
    }

    public function getQuery(): Builder
    {
        return $this->query;
    }

    public function setQuery(Builder $query): static
    {
        $this->query = $query;

        return $this;
    }

    public function getModelClass(): string
    {
        return $this->modelClass;
    }

    public function getRequest(): QueryBuilderRequest
    {
        return $this->request;
    }

    protected function getResourceName(): string
    {
        $model = new $this->modelClass;

        return $model->getTable();
    }

    public function get(array $columns = ['*'])
    {
        $this->applyAll();

        return $this->query->get($columns);
    }

    public function first()
    {
        $this->applyAll();

        return $this->query->first();
    }

    public function firstOrFail()
    {
        $this->applyAll();

        return $this->query->firstOrFail();
    }

    public function paginate()
    {
        $this->applyAll();

        $paginator = $this->applyPagination();

        return PaginationResult::fromPaginator($paginator);
    }

    public function simplePaginate()
    {
        $this->applyAll();

        $perPage = $this->perPage ?? $this->request->getPerPage() ?? config('mts-query.default_per_page', 15);

        $paginator = $this->query->simplePaginate($perPage);

        return PaginationResult::fromSimplePaginator($paginator);
    }

    public function count()
    {
        $this->applyFilters();
        $this->applySearch();

        return $this->query->count();
    }

    public function exists()
    {
        $this->applyFilters();
        $this->applySearch();

        return $this->query->exists();
    }

    public function toQueryResult()
    {
        $items = $this->get();

        return new QueryResult(
            items: $items,
            appliedFilters: $this->getAppliedFilters(),
            appliedSorts: $this->getAppliedSorts(),
            appliedIncludes: $this->getAppliedIncludes(),
            searchTerm: $this->getSearchTerm(),
        );
    }

    public function toPaginatedQueryResult()
    {
        $paginator = $this->paginate();

        return new QueryResult(
            items: collect($paginator->paginator->items()),
            appliedFilters: $this->getAppliedFilters(),
            appliedSorts: $this->getAppliedSorts(),
            appliedIncludes: $this->getAppliedIncludes(),
            searchTerm: $this->getSearchTerm(),
            pagination: $paginator,
        );
    }

    protected function applyAll(): void
    {
        $this->applyFilters();
        $this->applySorts();
        $this->applyIncludes();
        $this->applyFields();
        $this->applySearch();
    }

    public function options(array $options): static
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function allowUnauthenticatedFilters(): static
    {
        $this->options['ignore_invalid_filters'] = true;

        return $this;
    }

    public function __call(string $method, array $parameters)
    {
        return $this->forwardCallTo($this->query, $method, $parameters);
    }
}
