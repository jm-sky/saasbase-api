<?php

namespace App\Domain\Chat\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Concerns\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string  $id
 * @property string  $chat_room_id
 * @property string  $user_id
 * @property ?string $parent_id
 * @property string  $content
 * @property ?Carbon $edited_at
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 */
class ChatMessage extends BaseModel
{
    // use BelongsToTenant;

    protected $fillable = [
        'chat_room_id',
        'user_id',
        'parent_id',
        'content',
        'edited_at',
    ];

    public function chatRoom(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\Auth\Models\User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
