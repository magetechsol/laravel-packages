<?php

declare(strict_types=1);

namespace MageTech\Audit\Support;

final readonly class ActorData
{
    public function __construct(
        public ?string $type = null,
        public int|string|null $id = null,
        public ?string $name = null,
        public ?string $email = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'] ?? null,
            id: $data['id'] ?? null,
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }

    public function isEmpty(): bool
    {
        return $this->type === null && $this->id === null;
    }
}
