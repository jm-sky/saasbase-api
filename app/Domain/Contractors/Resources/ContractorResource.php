<?php

namespace App\Domain\Contractors\Resources;

use App\Domain\Common\Resources\MediaResource;
use App\Domain\Contractors\Models\Contractor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Contractor $this->resource */
        $logoMedia = $this->getFirstMedia('logo');

        return [
            'id'          => $this->id,
            'tenantId'    => $this->tenant_id,
            'name'        => $this->name,
            'email'       => $this->email,
            'phone'       => $this->phone,
            'website'     => $this->website,
            'country'     => $this->country,
            'taxId'       => $this->tax_id,
            'description' => $this->description,
            'isActive'    => $this->is_active,
            'isBuyer'     => $this->is_buyer,
            'isSupplier'  => $this->is_supplier,
            'createdAt'   => $this->created_at?->toIso8601String(),
            'updatedAt'   => $this->updated_at?->toIso8601String(),
            'deletedAt'   => $this->deleted_at?->toIso8601String(),
            'logoUrl'     => $logoMedia ? $this->getMediaUrl('logo', $logoMedia->file_name) : null,
            'logo'        => $logoMedia ? new MediaResource($logoMedia) : null,
            'tags'        => method_exists($this->resource, 'getTagNames') ? $this->getTagNames() : [],
        ];
    }
}
