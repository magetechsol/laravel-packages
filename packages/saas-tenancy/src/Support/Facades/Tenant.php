<?php

declare(strict_types=1);

namespace MageTech\SaaS\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MageTech\SaaS\TenantManager;

/**
 * @method static \MageTech\SaaS\Models\Tenant identify()
 * @method static \MageTech\SaaS\Models\Tenant getTenant()
 * @method static string|null getTenantId()
 * @method static string|null getTenantKey()
 * @method static bool isActive()
 * @method static bool isSuspended()
 * @method static \MageTech\SaaS\Models\Tenant create(array $data)
 * @method static void activate(\MageTech\SaaS\Models\Tenant $tenant)
 * @method static void suspend(\MageTech\SaaS\Models\Tenant $tenant, ?string $reason = null)
 * @method static void delete(\MageTech\SaaS\Models\Tenant $tenant)
 * @method static void migrate(\MageTech\SaaS\Models\Tenant $tenant)
 * @method static void migrateAll()
 * @method static void reset()
 *
 * @see \MageTech\SaaS\TenantManager
 */
class Tenant extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return TenantManager::class;
    }
}
