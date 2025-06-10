<?php

namespace App\Domain\Common\Resources;

use App\Domain\Auth\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserProfileTenantScopedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var User $this->resource */
        return [
            'id'           => $this->id,
            'name'         => $this->fullName,
            'email'        => $this->tenantScopedEmail,
            'phone'        => $this->tenantScopedPhone,
            'birthDate'    => $this->tenantScopedBirthDate,

            // ------------
            'bio'          => $this->profile?->bio,
            'location'     => $this->profile?->location,
            'position'     => $this->profile?->position,
            'website'      => $this->profile?->website,
            'socialLinks'  => $this->profile?->social_links ?? [],
            'skills'       => UserSkillPreviewResource::collection($this->skills),

            'avatarUrl'    => $this->getMediaSignedUrl('profile'),
            'createdAt'    => $this->created_at->toIso8601String(),
        ];
    }
}
