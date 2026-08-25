<?php

declare(strict_types=1);

namespace MageTech\Workflow\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class User extends Model
{
    protected $table = 'test_users';

    protected $fillable = [
        'name',
        'email',
        'role',
    ];

    public static function createTable(): void
    {
        if (! Schema::hasTable('test_users')) {
            Schema::create('test_users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('role')->default('user');
                $table->timestamps();
            });
        }
    }

    public static function dropTable(): void
    {
        Schema::dropIfExists('test_users');
    }
}
