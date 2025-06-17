<?php

namespace App\Domain\Auth\Resources;

use App\Domain\Auth\Models\UserSettings;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UserSettings
 */
class UserSettingsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var UserSettings $this->resource */
        return [
            'id'                 => $this->id,
            'userId'             => $this->user_id,
            'language'           => $this->language,
            'theme'              => $this->theme,
            'timezone'           => $this->timezone,
            'twoFactorEnabled'   => $this->two_factor_enabled,
            'twoFactorConfirmed' => $this->two_factor_confirmed,
            'preferences'        => $this->preferences,
        ];
    }
}
