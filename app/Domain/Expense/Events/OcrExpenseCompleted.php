<?php

namespace App\Domain\Expense\Events;

use App\Domain\Auth\Models\User;
use App\Domain\Expense\Models\Expense;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class OcrExpenseCompleted implements ShouldBroadcastNow
{
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public User $user,
        public Expense $expense
    ) {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("users.{$this->user->id}.notifications")];
    }

    public function broadcastWith(): array
    {
        return [
            'type' => 'ocrExpenseCompleted',
            'id'   => $this->expense->id,
        ];
    }

    public function broadcastAs(): string
    {
        return 'OcrExpenseCompleted';
    }
}
