<?php

namespace App\Domain\Auth\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserIdentityDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'userId'           => $this->user_id,
            'type'             => $this->type,
            'number'           => $this->number,
            'country'          => $this->country,
            'issuedAt'         => $this->issued_at,
            'expiresAt'        => $this->expires_at,
            'isVerified'       => $this->is_verified,
            'verifiedAt'       => $this->verified_at,
            'verifiedBy'       => $this->verified_by,
            'meta'             => $this->meta,
            'documentImageUrl' => $this->getFirstMediaUrl('document_images'),
            'createdAt'        => $this->created_at,
            'updatedAt'        => $this->updated_at,
        ];
    }
}
