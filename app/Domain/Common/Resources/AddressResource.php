<?php

namespace App\Domain\Common\Resources;

use App\Domain\Common\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Address $resource
 */
class AddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var Address $this->resource */
        return [
            'id'              => $this->id,
            'tenantId'        => $this->tenant_id,
            'country'         => $this->country,
            'postalCode'      => $this->postal_code,
            'city'            => $this->city,
            'street'          => $this->street,
            'building'        => $this->building,
            'flat'            => $this->flat,
            'description'     => $this->description,
            'type'            => $this->type,
            'isDefault'       => $this->is_default,
            'addressableId'   => $this->addressable_id,
            'addressableType' => $this->addressable_type,
            'createdAt'       => $this->created_at?->toIso8601String(),
            'updatedAt'       => $this->updated_at?->toIso8601String(),
        ];
    }
}
