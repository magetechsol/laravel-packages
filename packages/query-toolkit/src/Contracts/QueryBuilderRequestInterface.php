<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Contracts;

use Illuminate\Http\Request;

interface QueryBuilderRequestInterface
{
    public function getFilter(string $name): mixed;

    public function getFilters(): array;

    public function getSort(): ?string;

    public function getIncludes(): array;

    public function getFields(string $resourceName): ?array;

    public function getSearch(): ?string;

    public function getPerPage(): ?int;

    public function getPage(): ?int;

    public function hasFilter(string $name): bool;

    public function hasInclude(string $name): bool;

    public function getRequest(): Request;
}
