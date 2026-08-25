<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Exceptions;

class AiPromptNotFoundException extends AiGatewayException
{
    public static function named(string $name): static
    {
        return static::promptNotFound($name);
    }

    public static function versioned(string $name, int $version): static
    {
        return new static(
            "AI prompt [{$name}] version [{$version}] not found."
        );
    }
}
