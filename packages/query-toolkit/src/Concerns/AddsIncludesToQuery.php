<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Concerns;

use Illuminate\Database\Eloquent\Builder;
use MageTech\QueryToolkit\AllowedInclude;
use MageTech\QueryToolkit\Contracts\IncludeInterface;
use MageTech\QueryToolkit\Exceptions\InvalidFilterQuery;

trait AddsIncludesToQuery
{
    protected array $allowedIncludes = [];

    protected array $appliedIncludes = [];

    public function allowedIncludes(array $includes): static
    {
        foreach ($includes as $key => $value) {
            if (is_int($key) && is_string($value)) {
                $this->allowedIncludes[$value] = AllowedInclude::key($value);
            } elseif ($value instanceof AllowedInclude) {
                $this->allowedIncludes[$value->getName()] = $value;
            } elseif (is_string($key) && $value instanceof IncludeInterface) {
                $this->allowedIncludes[$key] = AllowedInclude::custom($key, $value);
            }
        }

        return $this;
    }

    protected function applyIncludes(): static
    {
        $includes = $this->request->getIncludes();

        foreach ($includes as $include) {
            if (! $this->isIncludeAllowed($include)) {
                if (! $this->options['ignore_invalid_filters']) {
                    throw InvalidFilterQuery::disallowedInclude($include, array_keys($this->allowedIncludes));
                }
                $this->skippedFilters[] = 'Include not allowed';
                continue;
            }

            $allowedInclude = $this->getAllowedInclude($include);
            $this->applyInclude($allowedInclude);
        }

        return $this;
    }

    protected function isIncludeAllowed(string $name): bool
    {
        return isset($this->allowedIncludes[$name]);
    }

    protected function getAllowedInclude(string $name): AllowedInclude
    {
        return $this->allowedIncludes[$name];
    }

    protected function applyInclude(AllowedInclude $allowedInclude): void
    {
        $include = $allowedInclude->getInclude();
        $this->query = $include->apply($this->query);
        $this->appliedIncludes[] = $allowedInclude->getName();
    }

    public function getAppliedIncludes(): array
    {
        return $this->appliedIncludes;
    }
}
