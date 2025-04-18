<?php

namespace App\Domain\Auth\DTOs;

use App\Domain\Auth\Models\UserSettings;
use Spatie\LaravelData\Data;

class UserSettingsDTO extends Data
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $userId,
        public readonly ?string $language,
        public readonly ?string $theme,
        public readonly ?string $timezone,
        public readonly bool $twoFactorEnabled,
        public readonly bool $twoFactorConfirmed,
        public readonly ?array $preferences,
    ) {
    }

    public static function fromModel(UserSettings $model): self
    {
        return new self(
            id: $model->id,
            userId: $model->user_id,
            language: $model->language,
            theme: $model->theme,
            timezone: $model->timezone,
            twoFactorEnabled: $model->two_factor_enabled,
            twoFactorConfirmed: $model->two_factor_confirmed,
            preferences: $model->preferences,
        );
    }
}
