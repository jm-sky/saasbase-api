<?php

namespace App\Domain\Users\Resources;

use App\Domain\Users\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /* @var UserProfile $this */
        return [
            'id'          => $this->id,
            'userId'      => $this->user_id,
            // 'avatarUrl'   => $this->user->getMediaSignedUrl('profile'),
            'bio'         => $this->bio,
            'location'    => $this->location,
            'birthDate'   => $this->birth_date,
            'position'    => $this->position,
            'website'     => $this->website,
            'socialLinks' => $this->social_links,
            'createdAt'   => $this->created_at,
            'updatedAt'   => $this->updated_at,
        ];
    }
}
