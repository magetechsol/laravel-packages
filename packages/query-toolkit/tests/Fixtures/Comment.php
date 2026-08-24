<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    protected  = ['post_id', 'body'];

    public function post(): BelongsTo
    {
        return ->belongsTo(Post::class);
    }
}
