<?php

declare(strict_types=1);

namespace MageTech\Workflow\DTOs;

readonly class StepResult
{
    public function __construct(
        public bool $success,
        public ?array $data = null,
        public ?string $error = null,
    ) {
    }

    public static function success(array $data = []): static
    {
        return new static(success: true, data: $data);
    }

    public static function failure(string $error): static
    {
        return new static(success: false, error: $error);
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'data' => $this->data,
            'error' => $this->error,
        ];
    }
}
