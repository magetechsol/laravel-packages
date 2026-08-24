<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Middleware;

use Closure;
use Illuminate\Http\Request;
use MageTech\QueryToolkit\Exceptions\InvalidFilterQuery;
use MageTech\QueryToolkit\Exceptions\PaginationException;
use Symfony\Component\HttpFoundation\Response;

class ValidateQueryParameters
{
    public function handle(Request $request, Closure $next): Response
    {
        $this->validateFilters($request);
        $this->validateSort($request);
        $this->validateIncludes($request);
        $this->validatePagination($request);

        return $next($request);
    }

    protected function validateFilters(Request $request): void
    {
        $maxFilters = config('mts-query.security.max_filters_per_request', 20);
        $filters = $request->input(config('mts-query.parameters.filter', 'filter'), []);

        if (is_array($filters) && count($filters) > $maxFilters) {
            throw InvalidFilterQuery::invalidFilterValue(
                'filters',
                count($filters),
                "Maximum {$maxFilters} filters allowed per request"
            );
        }
    }

    protected function validateSort(Request $request): void
    {
        $maxSorts = config('mts-query.security.max_sorts_per_request', 5);
        $sort = $request->input(config('mts-query.parameters.sort', 'sort'), '');

        if ($sort) {
            $sorts = array_map('trim', explode(',', $sort));

            if (count($sorts) > $maxSorts) {
                throw InvalidFilterQuery::invalidSortDirection(
                    "Maximum {$maxSorts} sort fields allowed per request"
                );
            }
        }
    }

    protected function validateIncludes(Request $request): void
    {
        $maxIncludes = config('mts-query.security.max_includes_per_request', 10);
        $include = $request->input(config('mts-query.parameters.include', 'include'), '');

        if ($include) {
            $includes = array_map('trim', explode(',', $include));

            if (count($includes) > $maxIncludes) {
                throw InvalidFilterQuery::invalidFilterValue(
                    'include',
                    count($includes),
                    "Maximum {$maxIncludes} includes allowed per request"
                );
            }
        }
    }

    protected function validatePagination(Request $request): void
    {
        $maxPerPage = config('mts-query.max_per_page', 100);
        $perPage = $request->input(config('mts-query.parameters.per_page', 'per_page'));

        if ($perPage && $perPage > $maxPerPage) {
            throw PaginationException::perPageExceedsMaximum((int) $perPage, $maxPerPage);
        }
    }
}
