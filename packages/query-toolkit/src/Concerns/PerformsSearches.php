<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Concerns;

use Illuminate\Database\Eloquent\Builder;
use MageTech\QueryToolkit\Contracts\SearchInterface;
use MageTech\QueryToolkit\Enums\SearchDriver;
use MageTech\QueryToolkit\Exceptions\InvalidSearchQuery;
use MageTech\QueryToolkit\Searches\FullTextSearch;
use MageTech\QueryToolkit\Searches\LikeSearch;

trait PerformsSearches
{
    protected ?SearchInterface $searchDriver = null;

    protected array $searchableFields = [];

    protected ?string $searchTerm = null;

    public function searchable(array $fields, SearchDriver $driver = SearchDriver::Like, array $weightedFields = []): static
    {
        $this->searchableFields = $fields;

        $this->searchDriver = match ($driver) {
            SearchDriver::Like => LikeSearch::make($fields, $weightedFields),
            SearchDriver::FullText => FullTextSearch::make($fields, $weightedFields),
        };

        return $this;
    }

    public function searchUsing(SearchInterface $driver): static
    {
        $this->searchDriver = $driver;

        return $this;
    }

    protected function applySearch(): static
    {
        $term = $this->request->getSearch();

        if (is_null($term) || $term === '' || is_null($this->searchDriver)) {
            return $this;
        }

        $this->searchTerm = $term;
        $this->query = $this->searchDriver->apply($this->query, $term);

        return $this;
    }

    public function getSearchTerm(): ?string
    {
        return $this->searchTerm;
    }

    public function getSearchableFields(): array
    {
        return $this->searchableFields;
    }
}
