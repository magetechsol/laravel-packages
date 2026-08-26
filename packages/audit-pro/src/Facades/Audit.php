<?php

declare(strict_types=1);

namespace MageTech\Audit\Facades;

use Illuminate\Support\Facades\Facade;
use MageTech\Audit\Services\Auditor;

/**
 * @method static \MageTech\Audit\Services\Audit record()
 * @method static \MageTech\Audit\Services\Audit event(string $event)
 * @method static string beginBatch()
 * @method static void endBatch()
 * @method static \MageTech\Audit\Contracts\AuditQuery query()
 * @method static void recordModelEvent(string $event, \Illuminate\Database\Eloquent\Model $model, ?array $oldValues = null, ?array $newValues = null)
 * @method static void recordLogin(\Illuminate\Database\Eloquent\Model $user, array $metadata = [])
 * @method static void recordLogout(\Illuminate\Database\Eloquent\Model $user, array $metadata = [])
 * @method static void recordFailedLogin(string $email, array $metadata = [])
 *
 * @see \MageTech\Audit\Services\Auditor
 */
class Audit extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Auditor::class;
    }
}
