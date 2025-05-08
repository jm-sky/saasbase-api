<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.room.{roomId}', function ($user, $roomId) {
    return App\Domain\Chat\Models\ChatParticipant::where('chat_room_id', $roomId)
        ->where('user_id', $user->id)
        ->exists()
    ;
});
