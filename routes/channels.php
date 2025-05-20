<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('users.{userId}.notifications', function ($user, $userId) {
    return (string) $user->id === (string) $userId;
});

Broadcast::channel('chat.room.{roomId}', function ($user, $roomId) {
    return App\Domain\Chat\Models\ChatParticipant::where('chat_room_id', $roomId)
        ->where('user_id', $user->id)
        ->exists()
    ;
});

Broadcast::channel('chat.ai.{userId}', function ($user, $userId) {
    return (string) $user->id === (string) $userId;
});
