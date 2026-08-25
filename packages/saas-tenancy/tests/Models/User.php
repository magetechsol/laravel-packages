<?php

declare(strict_types=1);

namespace MageTech\SaaS\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';

    protected $guarded = [];
}
