<?php

namespace App\Domain\Common\Resources;

use App\Domain\Common\Models\Contact;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Contact
 */
class ContactResource extends JsonResource
{
    public function toArray($request): array
    {
        $profileImage = $this->getFirstMedia('profile');

        return [
            'id'               => $this->id,
            'tenantId'         => $this->tenant_id,
            'firstName'        => $this->first_name,
            'lastName'         => $this->last_name,
            'position'         => $this->position,
            'email'            => $this->email,
            'phoneNumber'      => $this->phone_number,
            'emails'           => $this->emails,
            'phoneNumbers'     => $this->phone_numbers,
            'notes'            => $this->notes,
            'userId'           => $this->user_id,
            'contactableId'    => $this->contactable_id,
            'contactableType'  => $this->contactable_type,
            'createdAt'        => $this->created_at?->toIso8601String(),
            'updatedAt'        => $this->updated_at?->toIso8601String(),
            'deletedAt'        => $this->deleted_at?->toIso8601String(),
            'tags'             => method_exists($this->resource, 'getTagNames') ? $this->getTagNames() : [],
            'profileImage'     => $profileImage ? new MediaResource($profileImage) : null,
        ];
    }
}
