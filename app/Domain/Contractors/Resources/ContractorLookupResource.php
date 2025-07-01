<?php

namespace App\Domain\Contractors\Resources;

use App\Domain\Common\Models\Media;
use App\Domain\Common\Resources\AddressResource;
use App\Domain\Common\Resources\MediaResource;
use App\Domain\Contractors\Models\Contractor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Contractor
 */
class ContractorLookupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var ?Media $logoMedia */
        $logoMedia = $this->getFirstMedia('logo');

        return [
            'id'                    => $this->id,
            'tenantId'              => $this->tenant_id,
            'name'                  => $this->name,
            'type'                  => $this->type,
            'email'                 => $this->email,
            'phone'                 => $this->phone,
            'country'               => $this->country,
            'vatId'                 => $this->vat_id,
            'taxId'                 => $this->tax_id,
            'regon'                 => $this->regon,
            'description'           => $this->description,
            'isActive'              => $this->is_active,
            'isBuyer'               => $this->is_buyer,
            'isSupplier'            => $this->is_supplier,
            'logoUrl'               => $logoMedia ? $this->getMediaSignedUrl('logo') : null,
            'logo'                  => $logoMedia ? new MediaResource($logoMedia) : null,
            'preferences'           => $this->preferences ? new ContractorPreferencesResource($this->preferences) : null,
            'defaultAddress'        => $this->defaultAddress ? new AddressResource($this->defaultAddress) : null,
        ];
    }
}
