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

class TenantInvitationAcceptedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected array $viaList = ['database', 'broadcast'];

    /**
     * @param ?string $locale
     */
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
        return $this->viaList;
    }

    public function viaDatabaseOnly(): self
    {
        $this->viaList = ['database'];

        return $this;
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'    => 'tenantInvitation.accepted',
            'title'   => __('notifications.tenant_invitation.accepted.title'),
            'message' => __('notifications.tenant_invitation.accepted.message', [
                'name'   => $this->invitation->invitedUser->full_name,
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

    public function broadcastOn(): array
    {
        return [new PrivateChannel("users.{$this->notifiable->id}.notifications")];
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
                'title'   => __('notifications.tenant_invitation.accepted.title'),
                'message' => __('notifications.tenant_invitation.accepted.message', [
                    'name'   => $this->invitation->invitedUser->full_name,
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
