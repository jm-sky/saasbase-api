<?php

namespace App\Domain\Tenant\Resources;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Models\OrgUnitUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 *
 * @property string      $fullName
 * @property OrgUnitUser $pivot
 */
class OrganizationUnitUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->full_name,
            'email'     => $this->email,
            'avatarUrl' => $this->getMediaSignedUrl('profile'),
            'role'      => $this->pivot->role,
            'position'  => $this->pivot->position?->name,
        ];
    }
}
