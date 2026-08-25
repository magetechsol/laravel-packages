<?php

declare(strict_types=1);

namespace MageTech\SaaS\Exceptions;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TenantNotFoundException extends HttpException
{
    public static function make(?Request $request = null): static
    {
        $host = $request?->getHost() ?? 'unknown';

        return new static(404, "Tenant not found for domain [{$host}].");
    }
}
