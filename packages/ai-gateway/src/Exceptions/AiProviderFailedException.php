<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Exceptions;

use MageTech\AIGateway\DTOs\ResolvedModel;

class AiProviderFailedException extends AiGatewayException
{
    public static function allProvidersFailed(ResolvedModel $resolved, \Throwable $previous): static
    {
        return new static(
            "All AI providers failed for model [{$resolved->model}]. Last error: {$previous->getMessage()}",
            previous: $previous,
        );
    }

    public static function providerFailed(string $provider, string $model, \Throwable $previous): static
    {
        return new static(
            "AI provider [{$provider}] failed for model [{$model}]. Error: {$previous->getMessage()}",
            previous: $previous,
        );
    }

    public static function timeout(string $provider, string $model): static
    {
        return new static(
            "AI provider [{$provider}] timed out for model [{$model}]."
        );
    }
}
