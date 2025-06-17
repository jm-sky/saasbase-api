<?php

namespace App\Domain\Auth\Resources;

use App\Domain\Auth\Models\UserPersonalData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UserPersonalData
 */
class UserPersonalDataResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'userId'              => $this->user_id,
            'gender'              => $this->gender,
            'pesel'               => $this->pesel,
            'isGenderVerified'    => $this->is_gender_verified,
            'isBirthDateVerified' => $this->is_birth_date_verified,
            'isPeselVerified'     => $this->is_pesel_verified,
            'createdAt'           => $this->created_at,
            'updatedAt'           => $this->updated_at,
        ];
    }
}
