<?php

namespace App\Traits;

use App\Models\UuidDatabaseNotification;

trait UuidNotifiable
{
    use \Illuminate\Notifications\Notifiable {
        notifications as baseNotifications;
    }

    public function notifications()
    {
        return $this->morphMany(UuidDatabaseNotification::class, 'notifiable');
    }
}
