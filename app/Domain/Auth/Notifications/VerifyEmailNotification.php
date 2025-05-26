<?php

namespace App\Domain\Auth\Notifications;

use App\Domain\Auth\Models\EmailVerificationToken;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class VerifyEmailNotification extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    public function __construct(?string $locale = null)
    {
        if ($locale) {
            App::setLocale($locale);
        }
    }

    /**
     * Get the verification URL for the given notifiable.
     */
    protected function verificationUrl($notifiable): string
    {
        // Generate a new verification token
        $token = Str::random(64);

        // Store the token
        EmailVerificationToken::updateOrCreate(
            ['user_id' => $notifiable->id],
            ['token' => $token]
        );

        $frontendUrl = rtrim(config('app.frontend_url', config('app.url')), '/');
        $apiPrefix   = rtrim(config('app.api_prefix', '/api/v1'), '/');

        $url = route('verification.verify', [
            'token' => $token,
            'email' => $notifiable->email,
        ], absolute: false);

        $url = str_replace($apiPrefix, '', $url);

        // Return the frontend URL with token
        return $frontendUrl . $url;
    }

    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage())
            ->subject(__('notifications.email_verification.subject'))
            ->view('emails.verify-email', [
                'url'        => $verificationUrl,
                'notifiable' => $notifiable,
            ])
        ;
    }
}
