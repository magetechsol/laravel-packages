<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = [
        'name',
        'email',
        'is_active',
        'salary',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'salary' => 'decimal:2',
        ];
    }
}
