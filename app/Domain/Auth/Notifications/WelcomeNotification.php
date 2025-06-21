<?php

namespace App\Domain\Auth\Notifications;

use App\Domain\Auth\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $notifiable,
        public $locale = null
    ) {
        if ($locale) {
            App::setLocale($locale);
        }
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
            ->subject(__('notifications.welcome.subject', ['app' => $appName]))
            ->greeting(__('notifications.welcome.greeting', ['name' => $notifiable->full_name]))
            ->line(__('notifications.welcome.message', ['app' => $appName]))
            ->line(__('notifications.welcome.excitement'))
            ->action(__('notifications.welcome.dashboard_button'), $dashboardUrl)
            ->line(__('notifications.welcome.help'))
        ;
    }

    public function toDatabase($notifiable): array
    {
        $appName = Config::get('app.name');

        return [
            'type'    => 'welcome',
            'title'   => __('notifications.welcome.title', ['app' => $appName]),
            'message' => __('notifications.welcome.notification_message', ['name' => $notifiable->full_name]),
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
                'title'   => __('notifications.welcome.title', ['app' => $appName]),
                'message' => __('notifications.welcome.notification_message', ['name' => $this->notifiable->full_name]),
                'source'  => 'System',
            ],
            'readAt'    => null,
            'createdAt' => now(),
        ];
    }
}
