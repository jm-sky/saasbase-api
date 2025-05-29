<?php

namespace App\Domain\Users\Resources;

use App\Domain\Users\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPreferenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /* @var UserPreference $this */
        return [
            'id'                  => $this->id,
            'userId'              => $this->user_id,
            'language'            => $this->language,
            'timezone'            => $this->timezone,
            'decimalSeparator'    => $this->decimal_separator,
            'dateFormat'          => $this->date_format,
            'darkMode'            => $this->dark_mode,
            'isSoundEnabled'      => $this->is_sound_enabled,
            'isProfilePublic'     => $this->is_profile_public,
            'fieldVisibility'     => $this->field_visibility,
            'visibilityPerTenant' => $this->visibility_per_tenant,
            'createdAt'           => $this->created_at,
            'updatedAt'           => $this->updated_at,
        ];
    }
}
