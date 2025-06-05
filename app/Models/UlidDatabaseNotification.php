<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Notifications\DatabaseNotification;

class UlidDatabaseNotification extends DatabaseNotification
{
    use HasUlids;
}
