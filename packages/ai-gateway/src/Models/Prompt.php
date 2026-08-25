<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MageTech\AIGateway\DTOs\PromptTemplate;

class Prompt extends Model
{
    protected $table = 'mts_ai_prompts';

    protected $fillable = [
        'name',
        'version',
        'template',
        'variables',
        'model',
        'temperature',
        'max_tokens',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'metadata' => 'array',
            'temperature' => 'float',
            'max_tokens' => 'integer',
            'version' => 'integer',
        ];
    }

    public function toTemplate(): PromptTemplate
    {
        return new PromptTemplate(
            name: $this->name,
            version: $this->version,
            template: $this->template,
            variables: $this->variables ?? [],
            model: $this->model,
            temperature: $this->temperature,
            maxTokens: $this->max_tokens,
            status: $this->status,
            metadata: $this->metadata ?? [],
        );
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForName($query, string $name)
    {
        return $query->where('name', $name);
    }

    public function scopeLatestVersion($query)
    {
        return $query->latest('version');
    }
}
