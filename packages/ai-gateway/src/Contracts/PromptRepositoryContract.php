<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Contracts;

use Illuminate\Support\Collection;
use MageTech\AIGateway\DTOs\PromptTemplate;

interface PromptRepositoryContract
{
    public function get(string $name, ?int $version = null): PromptTemplate;

    public function create(array $data): PromptTemplate;

    public function version(string $name, int $version): PromptTemplate;

    public function all(string $name): Collection;

    public function update(string $name, int $version, array $data): PromptTemplate;

    public function latest(string $name): ?PromptTemplate;
}
