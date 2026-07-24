<?php

namespace App\Traits;

use App\Models\UlidDatabaseNotification;
use Illuminate\Notifications\Notifiable;

trait UlidNotifiable
{
    use Notifiable {
        notifications as baseNotifications;
    }

    public function notifications()
    {
        return $this->morphMany(UlidDatabaseNotification::class, 'notifiable');
    }
}
