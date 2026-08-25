<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Exceptions;

class AiModelNotAllowedException extends AiGatewayException
{
    public static function modelNotAllowed(string $model, ?int $tenantId = null): static
    {
        $tenantPart = $tenantId ? " for tenant [{$tenantId}]" : '';

        return new static(
            "Model [{$model}] is not allowed{$tenantPart}."
        );
    }

    public static function providerNotAllowed(string $provider): static
    {
        return new static(
            "Provider [{$provider}] is not enabled or not configured."
        );
    }
}
