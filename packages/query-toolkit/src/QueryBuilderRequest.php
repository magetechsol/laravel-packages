<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit;

use Illuminate\Http\Request;
use MageTech\QueryToolkit\Contracts\QueryBuilderRequestInterface;

class QueryBuilderRequest implements QueryBuilderRequestInterface
{
    protected Request $request;

    protected array $parameters;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->parameters = $this->parseParameters();
    }

    public static function fromRequest(Request $request): static
    {
        return new static($request);
    }

    public function getFilter(string $name): mixed
    {
        return $this->parameters['filter'][$name] ?? null;
    }

    public function getFilters(): array
    {
        return $this->parameters['filter'] ?? [];
    }

    public function getSort(): ?string
    {
        return $this->parameters['sort'] ?? null;
    }

    public function getIncludes(): array
    {
        $include = $this->parameters['include'] ?? '';

        if (is_string($include) && $include !== '') {
            return array_map('trim', explode(',', $include));
        }

        return [];
    }

    public function getFields(string $resourceName): ?array
    {
        $fields = $this->parameters['fields'] ?? [];

        if (isset($fields[$resourceName])) {
            $value = $fields[$resourceName];

            if (is_string($value)) {
                return array_map('trim', explode(',', $value));
            }

            return (array) $value;
        }

        return null;
    }

    public function getSearch(): ?string
    {
        $search = $this->parameters['search'] ?? null;

        if (is_string($search) && $search !== '') {
            return $search;
        }

        return null;
    }

    public function getPerPage(): ?int
    {
        $perPage = $this->parameters['per_page'] ?? null;

        if (is_numeric($perPage) && $perPage > 0) {
            return (int) $perPage;
        }

        return null;
    }

    public function getPage(): ?int
    {
        $page = $this->parameters['page'] ?? null;

        if (is_numeric($page) && $page > 0) {
            return (int) $page;
        }

        return null;
    }

    public function hasFilter(string $name): bool
    {
        return isset($this->parameters['filter'][$name]);
    }

    public function hasInclude(string $name): bool
    {
        $includes = $this->getIncludes();

        return in_array($name, $includes);
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function all(): array
    {
        return $this->parameters;
    }

    protected function parseParameters(): array
    {
        $paramKeys = config('mts-query.parameters', []);

        return [
            'filter' => $this->request->input($paramKeys['filter'] ?? 'filter', []),
            'sort' => $this->request->input($paramKeys['sort'] ?? 'sort', null),
            'include' => $this->request->input($paramKeys['include'] ?? 'include', ''),
            'fields' => $this->request->input($paramKeys['fields'] ?? 'fields', []),
            'search' => $this->request->input($paramKeys['search'] ?? 'search', null),
            'per_page' => $this->request->input($paramKeys['per_page'] ?? 'per_page', null),
            'page' => $this->request->input($paramKeys['page'] ?? 'page', null),
        ];
    }
}
