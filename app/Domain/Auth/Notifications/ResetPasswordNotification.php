<?php

namespace App\Domain\Auth\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $token,
        public $locale = null
    ) {
        if ($locale) {
            App::setLocale($locale);
        }
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Generate frontend URL for password reset
        $url = Config::get('app.frontend_url') . '/auth/reset-password?' . http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage())
            ->subject(__('notifications.password.reset.subject'))
            ->line(__('notifications.password.reset.message'))
            ->action(__('notifications.password.reset.button'), $url)
            ->line(__('notifications.password.reset.expiry', ['count' => config('auth.passwords.users.expire')]))
            ->line(__('notifications.password.reset.ignore'))
        ;
    }
}
