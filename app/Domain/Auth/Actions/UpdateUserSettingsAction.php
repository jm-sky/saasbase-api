<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DTOs\UserSettingsDTO;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Models\UserSettings;

class UpdateUserSettingsAction
{
    public function execute(User $user, array $data): UserSettingsDTO
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
            'language'    => $data['language'] ?? $settings->language,
            'theme'       => $data['theme'] ?? $settings->theme,
            'timezone'    => $data['timezone'] ?? $settings->timezone,
            'preferences' => array_merge($settings->preferences ?? [], $data['preferences'] ?? []),
        ]);

        return UserSettingsDTO::fromModel($settings->fresh());
    }
}
