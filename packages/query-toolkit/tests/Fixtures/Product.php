<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected  = ['name', 'sku', 'description', 'price', 'stock_quantity', 'is_active', 'metadata'];

    protected  = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];
}
