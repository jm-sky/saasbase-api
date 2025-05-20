<?php

namespace App\Domain\Tenant\Notifications;

use App\Domain\Tenant\Models\TenantInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public TenantInvitation $invitation)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $frontendUrl = config('app.frontend_url');
        $url         = $frontendUrl . '/login?tenantInvitationToken=' . $this->invitation->token;

        return (new MailMessage())
            ->subject('You are invited to join a tenant')
            ->greeting('Hello!')
            ->line('You have been invited to join a tenant in SaaSBase.')
            ->line('- Tenant: ' . $this->invitation->tenant->name)
            ->line('- Role: ' . $this->invitation->role)
            ->line('')
            ->line('You can accept the invitation by clicking the button below.')
            ->action('Accept Invitation', $url)
            ->line('If you did not expect this invitation, you can ignore this email.')
        ;
    }
}
