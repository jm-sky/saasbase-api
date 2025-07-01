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

class PasswordChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param ?string $locale
     */
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
        $appName = Config::get('app.name');

        return (new MailMessage())
            ->subject(__('notifications.password.changed.subject', ['app' => $appName]))
            ->greeting(__('notifications.password.changed.greeting', ['name' => $notifiable->full_name]))
            ->line(__('notifications.password.changed.message', ['app' => $appName]))
            ->line(__('notifications.password.changed.warning'))
            ->line(__('notifications.password.changed.help'))
        ;
    }

    public function toDatabase($notifiable): array
    {
        $appName = Config::get('app.name');

        return [
            'type'    => 'security.passwordChanged',
            'title'   => __('notifications.password.changed.title'),
            'message' => __('notifications.password.changed.message', ['app' => $appName]),
            'source'  => 'System',
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("users.{$this->notifiable->id}.notifications")];
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
                'type'    => 'security.passwordChanged',
                'title'   => __('notifications.password.changed.title'),
                'message' => __('notifications.password.changed.message', ['app' => $appName]),
                'source'  => 'System',
            ],
            'readAt'    => null,
            'createdAt' => now(),
        ];
    }
}
