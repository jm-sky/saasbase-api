<?php

namespace App\Domain\Contractors\Resources;

use App\Domain\Common\Models\Media;
use App\Domain\Common\Resources\MediaResource;
use App\Domain\Common\Resources\TagResource;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Utils\Resources\RegistryConfirmationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Contractor
 */
class ContractorResource extends JsonResource
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
            'website'               => $this->website,
            'country'               => $this->country,
            'vatId'                 => $this->vat_id,
            'taxId'                 => $this->tax_id,
            'regon'                 => $this->regon,
            'description'           => $this->description,
            'isActive'              => $this->is_active,
            'isBuyer'               => $this->is_buyer,
            'isSupplier'            => $this->is_supplier,
            'createdAt'             => $this->created_at?->toIso8601String(),
            'updatedAt'             => $this->updated_at?->toIso8601String(),
            'deletedAt'             => $this->deleted_at?->toIso8601String(),
            'logoUrl'               => $logoMedia ? $this->getMediaSignedUrl('logo') : null,
            'logo'                  => $logoMedia ? new MediaResource($logoMedia) : null,
            'tags'                  => TagResource::collection($this->tags),
            'preferences'           => $this->preferences ? new ContractorPreferencesResource($this->preferences) : null,
            'registryConfirmations' => $this->registryConfirmations?->count() ? RegistryConfirmationResource::collection($this->registryConfirmations) : null,
        ];
    }
}
