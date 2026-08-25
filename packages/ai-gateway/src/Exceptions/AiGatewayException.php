<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Exceptions;

use RuntimeException;

class AiGatewayException extends RuntimeException
{
    public static function configuration(string $message): static
    {
        return new static("AI Gateway configuration error: {$message}");
    }

    public static function promptNotFound(string $name): static
    {
        return new static("AI prompt [{$name}] not found.");
    }

    public static function promptNotActive(string $name): static
    {
        return new static("AI prompt [{$name}] is not active.");
    }
}
