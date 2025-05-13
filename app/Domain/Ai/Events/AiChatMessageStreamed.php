<?php

namespace App\Domain\Ai\Events;

use App\Domain\Ai\DTOs\StreamDeltaData;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class AiChatMessageStreamed implements ShouldBroadcastNow
{
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public string $userId,
        public StreamDeltaData $delta,
        public int $index
    ) {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('chat.ai.' . $this->userId)];
    }

    public function broadcastWith(): array
    {
        return $this->delta->toArray();
    }

    public function broadcastAs(): string
    {
        return 'AiChatMessageStreamed';
    }
}
