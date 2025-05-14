<?php

namespace App\Domain\Tenant\Notifications;

use App\Domain\Tenant\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Invitation $invitation)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = url('/api/v1/invitations/' . $this->invitation->token);

        return (new MailMessage())
            ->subject('You are invited to join a tenant')
            ->greeting('Hello!')
            ->line('You have been invited to join a tenant in SaaSBase.')
            ->action('Accept Invitation', $url)
            ->line('If you did not expect this invitation, you can ignore this email.')
        ;
    }
}
