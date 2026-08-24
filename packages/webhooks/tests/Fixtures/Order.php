<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'id',
        'total',
        'status',
        'customer_email',
    ];
}
