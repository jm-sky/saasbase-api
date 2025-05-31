<?php

namespace App\Domain\EDoreczenia\Resources;

use App\Domain\Common\Resources\UserPreviewResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EDoreczeniaCertificateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'tenantId'     => $this->tenant_id,
            'userId'       => $this->user_id,
            'provider'     => $this->provider,
            'serialNumber' => $this->serial_number,
            'validFrom'    => $this->valid_from,
            'validTo'      => $this->valid_to,
            'status'       => $this->status,
            'createdAt'    => $this->created_at,
            'updatedAt'    => $this->updated_at,
            'creator'      => new UserPreviewResource($this->whenLoaded('creator')),
        ];
    }
}
