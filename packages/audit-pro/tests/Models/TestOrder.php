<?php

declare(strict_types=1);

namespace MageTech\Audit\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use MageTech\Audit\Support\AuditableTrait;
use MageTech\Audit\Contracts\Auditable;

class TestOrder extends Model implements Auditable
{
    use AuditableTrait;

    protected $guarded = [];

    protected $table = 'orders';

    public function getAuditMetadata(): array
    {
        return [
            'source' => 'test',
        ];
    }

    public function getAuditTags(): array
    {
        return ['order'];
    }
}
