<?php

namespace App\Domain\Expense\Resources;

use App\Domain\Auth\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class ApprovalUserResource extends JsonResource
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
            'id'        => $this->id,
            'name'      => $this->full_name,
            'email'     => $this->email,
            'avatarUrl' => $this->getMediaSignedUrl('profile'),
        ];
    }
}
