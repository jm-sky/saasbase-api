<?php

namespace App\Domain\Feeds\Models;

use App\Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Tenants\Concerns\BelongsToTenant;
use League\CommonMark\CommonMarkConverter;
use Mews\Purifier\Facades\Purifier;

/**
 * Class Feed
 *
 * @property string $id
 * @property string $tenant_id
 * @property string $user_id
 * @property string $title
 * @property string $content
 * @property string|null $content_html
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read User $user
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Domain\Feeds\Models\FeedComment[] $comments
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Feed query()
 */
class Feed extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'title',
        'content',
        'content_html',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (Feed $model) {
            $cleanContent = Purifier::clean($model->content);
            $model->content = $cleanContent;

            $converter = new CommonMarkConverter();
            $model->content_html = $converter->convert($cleanContent)->getContent();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(FeedComment::class);
    }
}
