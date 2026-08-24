<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Concerns;

use Illuminate\Database\Eloquent\Builder;
use MageTech\QueryToolkit\Exceptions\InvalidFilterQuery;

trait AddsFieldsToQuery
{
    protected array $allowedFields = [];

    protected array $appliedFields = [];

    public function allowedFields(array $fields): static
    {
        foreach ($fields as $resource => $allowed) {
            $this->allowedFields[$resource] = $allowed;
        }

        return $this;
    }

    protected function applyFields(): static
    {
        $fields = $this->request->getFields($this->getResourceName());

        if (is_null($fields)) {
            return $this;
        }

        if (isset($this->allowedFields[$this->getResourceName()])) {
            $allowed = $this->allowedFields[$this->getResourceName()];
            $disallowed = array_diff($fields, $allowed);

            if (! empty($disallowed) && ! $this->options['ignore_invalid_filters']) {
                throw InvalidFilterQuery::disallowedField(
                    implode(', ', $disallowed),
                    $this->getResourceName(),
                    $allowed
                );
            }

            $fields = array_intersect($fields, $allowed);
        }

        if (! empty($fields)) {
            $this->query->select($fields);
            $this->appliedFields[$this->getResourceName()] = $fields;
        }

        return $this;
    }

    public function getAppliedFields(): array
    {
        return $this->appliedFields;
    }

    abstract protected function getResourceName(): string;
}
