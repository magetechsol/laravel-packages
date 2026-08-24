<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    protected  = ['user_id', 'title', 'body', 'is_published', 'category'];

    protected  = [
        'is_published' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return ->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return ->hasMany(Comment::class);
    }

    public function scopePublished()
    {
        return ->where('is_published', true);
    }
}
