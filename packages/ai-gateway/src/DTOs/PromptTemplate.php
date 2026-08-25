<?php

declare(strict_types=1);

namespace MageTech\AIGateway\DTOs;

class PromptTemplate
{
    public function __construct(
        public readonly string $name,
        public readonly int $version,
        public readonly string $template,
        public readonly array $variables = [],
        public readonly ?string $model = null,
        public readonly ?float $temperature = null,
        public readonly ?int $maxTokens = null,
        public readonly string $status = 'active',
        public readonly array $metadata = [],
    ) {}

    public function render(array $variables): string
    {
        $compiled = $this->template;

        foreach ($variables as $key => $value) {
            $compiled = str_replace("{{ {$key} }}", (string) $value, $compiled);
            $compiled = str_replace("{{{$key}}}", (string) $value, $compiled);
        }

        return $compiled;
    }

    public function requiredVariables(): array
    {
        preg_match_all('/\{\{\s*(\w+)\s*\}\}/', $this->template, $matches);

        return array_unique($matches[1] ?? []);
    }

    public function validateVariables(array $variables): bool
    {
        $required = $this->requiredVariables();

        foreach ($required as $var) {
            if (! array_key_exists($var, $variables)) {
                return false;
            }
        }

        return true;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'version' => $this->version,
            'template' => $this->template,
            'variables' => $this->variables,
            'model' => $this->model,
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxTokens,
            'status' => $this->status,
            'metadata' => $this->metadata,
        ];
    }
}
