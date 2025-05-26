<?php

namespace App\Domain\Tenant\Notifications;

use App\Domain\Tenant\Models\TenantInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;

class TenantInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public TenantInvitation $invitation,
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
        $url         = $frontendUrl . '/login?tenantInvitationToken=' . $this->invitation->token;

        return (new MailMessage())
            ->subject(__('notifications.tenant_invitation.subject'))
            ->greeting(__('notifications.tenant_invitation.greeting'))
            ->line(__('notifications.tenant_invitation.intro'))
            ->line(__('notifications.tenant_invitation.tenant_info', ['name' => $this->invitation->tenant->name]))
            ->line(__('notifications.tenant_invitation.role_info', ['role' => $this->invitation->role]))
            ->line('')
            ->line(__('notifications.tenant_invitation.accept_button'))
            ->action(__('notifications.tenant_invitation.accept_button'), $url)
            ->line(__('notifications.tenant_invitation.ignore_info'))
        ;
    }
}
