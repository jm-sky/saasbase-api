<?php

namespace App\Domain\Auth\DTOs;

use App\Domain\Auth\Models\UserSettings;
use App\Domain\Common\DTOs\BaseDTO;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<UserSettings>
 *
 * @property ?string $id                 UUID
 * @property string  $userId             UUID
 * @property ?string $language
 * @property ?string $theme
 * @property ?string $timezone
 * @property bool    $twoFactorEnabled
 * @property bool    $twoFactorConfirmed
 * @property ?array  $preferences
 */
class UserSettingsDTO extends BaseDTO
{
    public function __construct(
        public readonly string $userId,
        public readonly ?string $language,
        public readonly ?string $theme,
        public readonly ?string $timezone,
        public readonly bool $twoFactorEnabled,
        public readonly bool $twoFactorConfirmed,
        public readonly ?string $id = null,
        public readonly ?array $preferences = null,
    ) {
    }

    public static function fromModel(Model $model): static
    {
        /* @var UserSettings $model */
        return new static(
            userId: $model->user_id,
            language: $model->language,
            theme: $model->theme,
            timezone: $model->timezone,
            twoFactorEnabled: $model->two_factor_enabled,
            twoFactorConfirmed: $model->two_factor_confirmed,
            id: $model->id,
            preferences: $model->preferences,
        );
    }

    public static function fromArray(array $data): static
    {
        return new static(
            userId: $data['user_id'],
            language: $data['language'] ?? null,
            theme: $data['theme'] ?? null,
            timezone: $data['timezone'] ?? null,
            twoFactorEnabled: $data['two_factor_enabled'],
            twoFactorConfirmed: $data['two_factor_confirmed'],
            id: $data['id'] ?? null,
            preferences: $data['preferences'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'                 => $this->id,
            'userId'             => $this->userId,
            'language'           => $this->language,
            'theme'              => $this->theme,
            'timezone'           => $this->timezone,
            'twoFactorEnabled'   => $this->twoFactorEnabled,
            'twoFactorConfirmed' => $this->twoFactorConfirmed,
            'preferences'        => $this->preferences,
        ];
    }
}
