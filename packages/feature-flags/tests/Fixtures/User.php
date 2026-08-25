<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'test_users';

    protected $fillable = [
        'name',
        'email',
        'role',
        'team_id',
        'organization_id',
    ];

    protected $guarded = [];

    public static function createTable(): void
    {
        \Illuminate\Support\Facades\Schema::create('test_users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('role')->default('user');
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->timestamps();
        });
    }

    public static function dropTable(): void
    {
        \Illuminate\Support\Facades\Schema::dropIfExists('test_users');
    }

    public function getRoleNames()
    {
        return collect([$this->role]);
    }

    public function hasPermissionTo(string $permission): bool
    {
        return $this->role === 'admin';
    }
}
