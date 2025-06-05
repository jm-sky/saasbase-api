<?php

namespace App\Traits;

use App\Models\UlidDatabaseNotification;

trait UlidNotifiable
{
    use \Illuminate\Notifications\Notifiable {
        notifications as baseNotifications;
    }

    public function notifications()
    {
        return $this->morphMany(UlidDatabaseNotification::class, 'notifiable');
    }
}
