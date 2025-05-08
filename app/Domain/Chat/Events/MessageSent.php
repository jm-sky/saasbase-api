<?php

namespace App\Domain\Chat\Events;

use App\Domain\Chat\DTOs\ChatMessageDTO;
use App\Domain\Chat\Models\ChatMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public ChatMessage $message)
    {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('chat.room.' . $this->message->chat_room_id)];
    }

    public function broadcastWith(): array
    {
        return [
            'data' => ChatMessageDTO::fromModel($this->message)->toArray(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'Chat\\MessageSent';
    }
}
