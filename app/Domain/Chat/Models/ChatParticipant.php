<?php

namespace App\Domain\Chat\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string    $id
 * @property string    $chat_room_id
 * @property string    $user_id
 * @property string    $role
 * @property Carbon    $joined_at
 * @property ?Carbon   $last_read_at
 * @property Carbon    $created_at
 * @property Carbon    $updated_at
 * @property ?ChatRoom $chatRoom
 * @property ?User     $user
 */
class ChatParticipant extends BaseModel
{
    // use BelongsToTenant;

    protected $fillable = [
        'chat_room_id',
        'user_id',
        'role',
        'joined_at',
        'last_read_at',
    ];

    public function chatRoom(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
