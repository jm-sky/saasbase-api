<?php

namespace App\Domain\Auth\Notifications;

use App\Domain\Auth\Models\ApplicationInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;

class ApplicationInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ApplicationInvitation $invitation,
        public $locale = null
    ) {
        if ($locale) {
            App::setLocale($locale);
        }
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
            ->subject(__('notifications.application_invitation.subject'))
            ->greeting(__('notifications.application_invitation.greeting'))
            ->line(__('notifications.application_invitation.intro'))
            ->line('')
            ->line(__('notifications.application_invitation.accept_button'))
            ->action(__('notifications.application_invitation.accept_button'), $url)
            ->line(__('notifications.application_invitation.ignore_info'))
        ;
    }
}
