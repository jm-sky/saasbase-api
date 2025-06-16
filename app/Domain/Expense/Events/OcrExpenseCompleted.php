<?php

namespace App\Domain\Expense\Events;

use App\Domain\Auth\Models\User;
use App\Domain\Expense\Models\Expense;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;

class OcrExpenseCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $notifiable,
        public Expense $expense
    ) {
    }

    public function via($notifiable): array
    {
        return ['broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        $appName = Config::get('app.name');

        return [
            'type'      => 'ocrExpenseCompleted',
            'id'        => $this->expense->id,
            'model'     => 'expense',
            'createdAt' => now(),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("users.{$this->notifiable->id}.notifications");
    }

    public function broadcastAs(): string
    {
        return 'notifications';
    }

    public function broadcastWith()
    {
        $appName = Config::get('app.name');

        return [
            'id'      => $this->id,
            'data'    => [
                'type'    => 'ocrExpenseCompleted',
                'id'      => $this->expense->id,
                'model'   => 'expense',
            ],
            'readAt'    => null,
            'createdAt' => now(),
        ];
    }
}
