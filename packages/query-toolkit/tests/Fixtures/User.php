<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    protected  = ['name', 'email', 'is_active', 'salary', 'status'];

    protected  = [
        'is_active' => 'boolean',
        'salary' => 'decimal:2',
    ];

    public function posts(): HasMany
    {
        return ->hasMany(Post::class);
    }

    public function activePosts(): HasMany
    {
        return ->posts()->where('is_published', true);
    }

    public function scopeActive()
    {
        return ->where('is_active', true);
    }

    public function scopeInactive()
    {
        return ->where('is_active', false);
    }
}
