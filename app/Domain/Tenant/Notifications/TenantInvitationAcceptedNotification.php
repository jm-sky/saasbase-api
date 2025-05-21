<?php

namespace App\Domain\Tenant\Notifications;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Models\TenantInvitation;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class TenantInvitationAcceptedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $notifiable,
        public TenantInvitation $invitation,
    ) {
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'    => 'tenantInvitation.accepted',
            'title'   => 'Your invitation has been accepted!',
            'message' => "Someonehas joined {$this->invitation->tenant->name} as {$this->invitation->role}!",
            'source'  => 'System',
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
        return [
            'id'      => $this->id,
            'data'    => [
                'type'    => 'tenantInvitation.accepted',
                'title'   => 'Your invitation has been accepted!',
                'message' => "Someone has joined {$this->invitation->tenant->name} as {$this->invitation->role}!",
                'source'  => 'System',
            ],
            'readAt'    => null,
            'createdAt' => now(),
        ];
    }
}
