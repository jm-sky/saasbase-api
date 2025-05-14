<?php

namespace App\Domain\Common\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\DTOs\CommentMeta;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string      $id
 * @property ?string     $tenant_id
 * @property string      $user_id
 * @property string      $content
 * @property string      $commentable_id
 * @property string      $commentable_type
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property ?Carbon     $deleted_at
 * @property Model       $commentable
 * @property User        $user
 * @property CommentMeta $meta
 */
class Comment extends BaseModel
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'content',
        'commentable_id',
        'commentable_type',
        'meta',
    ];

    protected $casts = [
        'meta' => CommentMeta::class,
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
