<?php

namespace App\Domain\Chat\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string  $id
 * @property string  $temp_id
 * @property string  $chat_room_id
 * @property string  $user_id
 * @property ?string $parent_id
 * @property string  $content
 * @property string  $role
 * @property bool    $is_ai
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
        'temp_id',
        'role',
        'is_ai',
    ];

    protected $casts = [
        'is_ai' => 'boolean',
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
