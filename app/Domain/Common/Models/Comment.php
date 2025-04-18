<?php

namespace App\Domain\Common\Models;

use App\Domain\Auth\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string  $id
 * @property string  $user_id
 * @property string  $content
 * @property string  $commentable_id
 * @property string  $commentable_type
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 * @property ?Carbon $deleted_at
 * @property Model   $commentable
 * @property User    $user
 */
class Comment extends BaseModel
{
    protected $fillable = [
        'user_id',
        'content',
        'commentable_id',
        'commentable_type',
    ];

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
