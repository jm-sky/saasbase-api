<?php

namespace App\Domain\Common\Traits;

use App\Domain\Common\Models\Comment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HaveComments
{
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
