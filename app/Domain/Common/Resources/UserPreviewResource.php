<?php

namespace App\Domain\Common\Resources;

use App\Domain\Auth\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 *
 * @property string $fullName
 */
class UserPreviewResource extends JsonResource
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
        ];
    }
}
