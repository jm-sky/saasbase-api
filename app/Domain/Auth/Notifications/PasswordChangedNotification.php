<?php

namespace App\Domain\Auth\Notifications;

use App\Domain\Auth\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;

class PasswordChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $notifiable)
    {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable): MailMessage
    {
        $appName      = Config::get('app.name');
        $frontendUrl  = Config::get('app.frontend_url');

        return (new MailMessage())
            ->subject("Password changed for {$appName}!")
            ->greeting("Hi {$notifiable->fullName},")
            ->line("Your password has been changed for {$appName}.")
            ->line('If you did not change your password, please contact support.')
            ->line('If you have any questions, feel free to reach out anytime.')
        ;
    }

    public function toDatabase($notifiable): array
    {
        $appName = Config::get('app.name');

        return [
            'type'    => 'security.passwordChanged',
            'title'   => "Password changed for {$appName}!",
            'message' => "Your password has been changed for {$appName}.",
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
}
