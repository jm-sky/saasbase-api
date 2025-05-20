<?php

namespace App\Domain\Auth\Notifications;

use App\Domain\Auth\Models\ApplicationInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ApplicationInvitation $invitation)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $frontendUrl = config('app.frontend_url');
        $url         = $frontendUrl . '/login?applicationInvitationToken=' . $this->invitation->token;

        return (new MailMessage())
            ->subject('You are invited to join SaaSBase')
            ->greeting('Hello!')
            ->line('You have been invited to join SaaSBase.')
            ->line('')
            ->line('You can accept the invitation by clicking the button below.')
            ->action('Accept Invitation', $url)
            ->line('If you did not expect this invitation, you can ignore this email.')
        ;
    }
}
