<?php

declare(strict_types=1);

namespace MageTech\SaaS\Exceptions;

use RuntimeException;

class TenantMixingException extends RuntimeException
{
    public static function detected(string $expected, string $actual): static
    {
        return new static(
            "Tenant mixing detected. Expected [{$expected}], got [{$actual}]."
        );
    }
}
