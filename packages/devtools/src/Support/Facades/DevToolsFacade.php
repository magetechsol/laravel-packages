<?php

declare(strict_types=1);

namespace MageTech\DevTools\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MageTech\DevTools\DevTools;
use MageTech\DevTools\Enums\HealthStatus;

/**
 * @method static bool isEnabled()
 * @method static bool isDashboardEnabled()
 * @method static bool isCommandEnabled()
 * @method static bool isCollectorEnabled(string $name)
 * @method static bool isAllowedIp(?string $ip = null)
 * @method static bool hasPassword()
 * @method static bool verifyPassword(string $password)
 * @method static array getApplicationData()
 * @method static array getPerformanceData()
 * @method static array getSecurityData()
 * @method static array getPackageData()
 * @method static array getAllData()
 * @method static array getHealthStatus()
 * @method static HealthStatus getOverallHealth()
 *
 * @see DevTools
 */
class DevToolsFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return DevTools::class;
    }
}
