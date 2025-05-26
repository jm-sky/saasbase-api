<?php

namespace App\Domain\Tenant\Notifications;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Models\TenantInvitation;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;

class TenantInvitationRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $notifiable,
        public TenantInvitation $invitation,
        public $locale = null
    ) {
        if ($locale) {
            App::setLocale($locale);
        }
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'    => 'tenantInvitation.rejected',
            'title'   => __('notifications.tenant_invitation.rejected.title'),
            'message' => __('notifications.tenant_invitation.rejected.message', [
                'name'   => $this->invitation->invitedUser->fullName,
                'tenant' => $this->invitation->tenant->name,
                'role'   => $this->invitation->role,
            ]),
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
                'type'    => 'tenantInvitation.rejected',
                'title'   => __('notifications.tenant_invitation.rejected.title'),
                'message' => __('notifications.tenant_invitation.rejected.message', [
                    'name'   => $this->invitation->invitedUser->fullName,
                    'tenant' => $this->invitation->tenant->name,
                    'role'   => $this->invitation->role,
                ]),
                'source'  => 'System',
            ],
            'readAt'    => null,
            'createdAt' => now(),
        ];
    }
}
