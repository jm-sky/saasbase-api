<?php

namespace App\Domain\Users\Resources;

use App\Domain\Common\Resources\UserSkillPreviewResource;
use App\Domain\Users\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UserProfile
 */
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
            'skills'      => UserSkillPreviewResource::collection($this->user?->skills ?? collect()),
            'roles'       => $this->user?->roles->pluck('name'),
        ];
    }
}
