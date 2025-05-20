<?php

namespace App\Domain\Users\Policies;

use App\Domain\Auth\Models\User;
use App\Domain\Users\Models\SecurityEvent;

class SecurityEventPolicy
{
    public function view(User $user, SecurityEvent $event): bool
    {
        return $user->id === $event->user_id;
    }
}
