<?php

namespace App\Domain\Feeds\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use App\Domain\Common\Models\Comment;
use App\Domain\Tenant\Traits\BelongsToTenant;
use App\Services\PurifierService;
use App\Traits\HasProfanityCheck;
use Database\Factories\FeedFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use League\CommonMark\CommonMarkConverter;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Class Feed.
 *
 * @property string                                             $id
 * @property string                                             $tenant_id
 * @property string                                             $user_id
 * @property string                                             $title
 * @property string                                             $content
 * @property ?string                                            $content_html
 * @property ?Carbon                                            $created_at
 * @property ?Carbon                                            $updated_at
 * @property User                                               $user
 * @property \Illuminate\Database\Eloquent\Collection|Comment[] $comments
 */
class Feed extends BaseModel implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use BelongsToTenant;
    use HasProfanityCheck;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'title',
        'content',
        'content_html',
    ];

    protected $profanityCheckFields = ['content'];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (Feed $model) {
            $cleanContent   = PurifierService::clean($model->content);
            $model->content = $cleanContent;

            $converter           = new CommonMarkConverter();
            $model->content_html = $converter->convert($cleanContent)->getContent();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    protected static function newFactory()
    {
        return FeedFactory::new();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments');
    }
}
