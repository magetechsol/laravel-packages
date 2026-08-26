<?php

declare(strict_types=1);

namespace MageTech\Audit\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use MageTech\Audit\Support\AuditableTrait;
use MageTech\Audit\Contracts\Auditable;

class TestUser extends Model implements Auditable
{
    use AuditableTrait;

    protected $guarded = [];

    protected $table = 'users';

    protected $auditExclude = [
        'password',
        'remember_token',
    ];

    protected $auditMasked = [
        'email',
    ];
}
