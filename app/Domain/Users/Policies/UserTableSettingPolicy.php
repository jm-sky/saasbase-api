<?php

namespace App\Domain\Users\Policies;

use App\Domain\Auth\Models\User;
use App\Domain\Users\Models\UserTableSetting;

class UserTableSettingPolicy
{
    public function update(User $user, UserTableSetting $setting): bool
    {
        return $user->id === $setting->user_id;
    }

    public function delete(User $user, UserTableSetting $setting): bool
    {
        return $user->id === $setting->user_id;
    }
}
