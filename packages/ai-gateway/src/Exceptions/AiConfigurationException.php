<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Exceptions;

class AiConfigurationException extends AiGatewayException
{
    public static function missingProvider(string $provider): static
    {
        return new static(
            "AI provider [{$provider}] is not configured. Check your config/mts-ai.php and .env files."
        );
    }

    public static function missingApiKey(string $provider): static
    {
        return new static(
            "API key for AI provider [{$provider}] is not set. Check your .env file."
        );
    }

    public static function invalidConfig(string $key, string $reason): static
    {
        return new static(
            "Invalid AI Gateway configuration for [{$key}]: {$reason}"
        );
    }
}
