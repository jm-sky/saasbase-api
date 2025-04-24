<?php

namespace App\Domain\Auth\Events;

use App\Domain\Auth\Models\User;
use Illuminate\Queue\SerializesModels;

class UserCreated
{
    use SerializesModels;

    public User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
