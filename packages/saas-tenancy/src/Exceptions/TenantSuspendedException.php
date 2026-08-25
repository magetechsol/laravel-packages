<?php

declare(strict_types=1);

namespace MageTech\SaaS\Exceptions;

use MageTech\SaaS\Models\Tenant;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TenantSuspendedException extends HttpException
{
    public static function make(Tenant $tenant): static
    {
        $message = config('mts-saas.suspension.message', 'Your account has been suspended.');

        return new static(403, $message);
    }
}
