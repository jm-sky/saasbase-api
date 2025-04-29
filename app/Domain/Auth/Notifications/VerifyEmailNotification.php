<?php

namespace App\Domain\Auth\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Get the verification URL for the given notifiable.
     */
    protected function verificationUrl($notifiable): string
    {
        $frontendUrl = rtrim(config('app.frontend_url', config('app.url')), '/');
        $apiUrl = rtrim(config('app.api_url', config('app.url') . '/api'), '/');

        $verifyUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ],
            false // Don't encode URL
        );

        // Replace API URL with frontend URL
        return str_replace($apiUrl, $frontendUrl . '/auth/verify-email', $verifyUrl);
    }

    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify Email Address')
            ->view('emails.verify-email', [
                'url' => $verificationUrl,
                'notifiable' => $notifiable
            ]);
    }
}
