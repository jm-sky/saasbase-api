<?php

namespace App\Domain\Auth\Resources;

use App\Domain\Auth\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'firstName'          => $this->first_name,
            'lastName'           => $this->last_name,
            'email'              => $this->email,
            'avatarUrl'          => $this->getMediaSignedUrl('profile'),
            'description'        => $this->profile?->bio,
            'birthDate'          => $this->profile?->birth_date,
            'phone'              => $this->phone,
            'isAdmin'            => $this->is_admin,
            'isEmailVerified'    => $this->isEmailVerified(),
            'isTwoFactorEnabled' => $this->isTwoFactorEnabled(),
            'roles'              => $this->getRoleNames()->toArray(),
            'permissions'        => $this->getAllPermissions()->pluck('name')->toArray(),
            'createdAt'          => $this->created_at?->toIso8601String(),
            'updatedAt'          => $this->updated_at?->toIso8601String(),
            'deletedAt'          => $this->deleted_at?->toIso8601String(),
        ];
    }
}
