<?php

namespace App\Domain\Users\Policies;

use App\Domain\Auth\Models\User;
use App\Domain\Users\Models\TrustedDevice;

class TrustedDevicePolicy
{
    public function delete(User $user, TrustedDevice $device): bool
    {
        return $user->id === $device->user_id;
    }
}
