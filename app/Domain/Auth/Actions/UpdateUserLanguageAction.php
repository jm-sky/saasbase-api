<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DTOs\UserSettingsDTO;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Models\UserSettings;

class UpdateUserLanguageAction
{
    public function execute(User $user, string $language): UserSettingsDTO
    {
        $settings = $user->settings;

        if (!$settings) {
            $settings = UserSettings::create([
                'user_id'              => $user->id,
                'language'             => config('app.locale'),
                'theme'                => 'light',
                'timezone'             => config('app.timezone'),
                'two_factor_enabled'   => false,
                'two_factor_confirmed' => false,
                'preferences'          => [],
            ]);
        }

        $settings->update([
            'language' => $language,
        ]);

        return UserSettingsDTO::fromModel($settings->fresh());
    }
}
