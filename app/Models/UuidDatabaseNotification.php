<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Notifications\DatabaseNotification;

class UuidDatabaseNotification extends DatabaseNotification
{
    use HasUuids;
}
