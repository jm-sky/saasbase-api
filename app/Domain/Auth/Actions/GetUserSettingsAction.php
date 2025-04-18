<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DTOs\UserSettingsDTO;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Models\UserSettings;

class GetUserSettingsAction
{
    public function execute(User $user): UserSettingsDTO
    {
        $settings = $user->settings ?? UserSettings::create([
            'user_id' => $user->id,
            'language' => config('app.locale'),
            'theme' => 'light',
            'timezone' => config('app.timezone'),
            'two_factor_enabled' => false,
            'two_factor_confirmed' => false,
            'preferences' => [],
        ]);

        return UserSettingsDTO::fromModel($settings);
    }
}
