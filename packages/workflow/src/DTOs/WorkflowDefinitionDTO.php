<?php

declare(strict_types=1);

namespace MageTech\Workflow\DTOs;

use MageTech\Workflow\Enums\ApprovalType;

readonly class WorkflowDefinitionDTO
{
    public function __construct(
        public string $name,
        public ?string $description = null,
        /** @var array<int, array<string, mixed>> */
        public array $steps = [],
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'steps' => $this->steps,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            name: $data['name'],
            description: $data['description'] ?? null,
            steps: $data['steps'] ?? [],
        );
    }
}
