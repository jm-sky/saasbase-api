<?php

namespace App\Domain\Common\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\DTOs\CommentMeta;
use App\Domain\Common\Traits\HasActivityLog;
use App\Domain\Common\Traits\HasActivityLogging;
use App\Domain\Contractors\Enums\ContractorActivityType;
use App\Domain\Contractors\Models\Contractor;
use App\Traits\HasProfanityCheck;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

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
    use HasActivityLog;
    use HasActivityLogging;
    use HasProfanityCheck;

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

    protected $profanityCheckFields = ['content'];

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function canEdit(): bool
    {
        return Auth::user()?->id === $this->user_id;
    }

    public function canDelete(): bool
    {
        return Auth::user()?->id === $this->user_id;
    }

    protected static function booted()
    {
        static::created(function ($comment) {
            if (Contractor::class === $comment->commentable_type) {
                $comment->commentable->logModelActivity(ContractorActivityType::CommentCreated->value, $comment);
            }
        });

        static::updated(function ($comment) {
            if (Contractor::class === $comment->commentable_type) {
                $comment->commentable->logModelActivity(ContractorActivityType::CommentUpdated->value, $comment);
            }
        });

        static::deleted(function ($comment) {
            if (Contractor::class === $comment->commentable_type) {
                $comment->commentable->logModelActivity(ContractorActivityType::CommentDeleted->value, $comment);
            }
        });
    }
}
