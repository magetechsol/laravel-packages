<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Prompts;

use Illuminate\Config\Repository;
use Illuminate\Support\Collection;
use MageTech\AIGateway\Contracts\PromptRepositoryContract;
use MageTech\AIGateway\DTOs\PromptTemplate;
use MageTech\AIGateway\Exceptions\AiPromptNotFoundException;
use MageTech\AIGateway\Models\Prompt;

class PromptManager implements PromptRepositoryContract
{
    public function __construct(
        protected Repository $config,
    ) {}

    public function get(string $name, ?int $version = null): PromptTemplate
    {
        if ($this->config->get('mts-ai.prompts.storage') === 'cache') {
            return $this->getFromCache($name, $version);
        }

        return $this->getFromDatabase($name, $version);
    }

    protected function getFromDatabase(string $name, ?int $version): PromptTemplate
    {
        $query = Prompt::where('name', $name)
            ->where('status', 'active');

        if ($version !== null) {
            $query->where('version', $version);
        } else {
            $query->latest('version');
        }

        $prompt = $query->first();

        if (! $prompt) {
            throw AiPromptNotFoundException::named($name);
        }

        return $prompt->toTemplate();
    }

    protected function getFromCache(string $name, ?int $version): PromptTemplate
    {
        $cacheKey = $version
            ? "mts_ai:prompt:{$name}:v{$version}"
            : "mts_ai:prompt:{$name}:latest";

        $ttl = $this->config->get('mts-ai.prompts.cache_ttl', 60);

        return cache()->remember($cacheKey, $ttl * 60, function () use ($name, $version) {
            return $this->getFromDatabase($name, $version);
        });
    }

    public function create(array $data): PromptTemplate
    {
        $latestVersion = Prompt::where('name', $data['name'])
            ->max('version') ?? 0;

        $prompt = Prompt::create([
            'name' => $data['name'],
            'version' => $latestVersion + 1,
            'template' => $data['template'],
            'variables' => $data['variables'] ?? [],
            'model' => $data['model'] ?? null,
            'temperature' => $data['temperature'] ?? null,
            'max_tokens' => $data['max_tokens'] ?? null,
            'status' => $data['status'] ?? 'active',
            'metadata' => $data['metadata'] ?? [],
        ]);

        $this->clearCache($data['name']);

        return $prompt->toTemplate();
    }

    public function version(string $name, int $version): PromptTemplate
    {
        $prompt = Prompt::where('name', $name)
            ->where('version', $version)
            ->first();

        if (! $prompt) {
            throw AiPromptNotFoundException::versioned($name, $version);
        }

        return $prompt->toTemplate();
    }

    public function all(string $name): Collection
    {
        return Prompt::where('name', $name)
            ->orderBy('version', 'desc')
            ->get()
            ->map(fn (Prompt $prompt) => $prompt->toTemplate());
    }

    public function update(string $name, int $version, array $data): PromptTemplate
    {
        $prompt = Prompt::where('name', $name)
            ->where('version', $version)
            ->first();

        if (! $prompt) {
            throw AiPromptNotFoundException::versioned($name, $version);
        }

        $prompt->update($data);

        $this->clearCache($name);

        return $prompt->toTemplate();
    }

    public function latest(string $name): ?PromptTemplate
    {
        $prompt = Prompt::where('name', $name)
            ->where('status', 'active')
            ->latest('version')
            ->first();

        return $prompt?->toTemplate();
    }

    public function save(PromptTemplate $template): Prompt
    {
        return Prompt::updateOrCreate(
            [
                'name' => $template->name,
                'version' => $template->version,
            ],
            [
                'template' => $template->template,
                'variables' => $template->variables,
                'model' => $template->model,
                'temperature' => $template->temperature,
                'max_tokens' => $template->maxTokens,
                'status' => $template->status,
                'metadata' => $template->metadata,
            ]
        );
    }

    protected function clearCache(string $name): void
    {
        if ($this->config->get('mts-ai.prompts.storage') === 'cache') {
            cache()->forget("mts_ai:prompt:{$name}:latest");

            $versions = Prompt::where('name', $name)->pluck('version');

            foreach ($versions as $version) {
                cache()->forget("mts_ai:prompt:{$name}:v{$version}");
            }
        }
    }
}
