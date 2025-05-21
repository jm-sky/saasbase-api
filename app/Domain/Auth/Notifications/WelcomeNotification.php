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

class WelcomeNotification extends Notification implements ShouldQueue
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
        $dashboardUrl = "{$frontendUrl}/";

        return (new MailMessage())
            ->subject("Welcome to {$appName}!")
            ->greeting("Hi {$notifiable->fullName},")
            ->line("Thanks for signing up to {$appName}.")
            ->line('We’re thrilled to have you on board and can’t wait to see what you build.')
            ->action('Go to Dashboard', $dashboardUrl)
            ->line('If you have any questions, feel free to reach out anytime.')
        ;
    }

    public function toDatabase($notifiable): array
    {
        $appName = Config::get('app.name');

        return [
            'type'    => 'welcome',
            'title'   => "Welcome to {$appName}!",
            'message' => "Welcome {$notifiable->fullName}, we're glad you're here!",
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

    public function broadcastAs(): string
    {
        return 'notifications';
    }

    public function broadcastWith()
    {
        $appName = Config::get('app.name');

        return [
            'id'      => $this->id,
            'data'    => [
                'type'    => 'welcome',
                'title'   => "Welcome to {$appName}!",
                'message' => "Welcome {$this->notifiable->fullName}, we're glad you're here!",
                'source'  => 'System',
            ],
            'readAt'    => null,
            'createdAt' => now(),
        ];
    }
}
