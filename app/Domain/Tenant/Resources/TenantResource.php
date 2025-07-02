<?php

namespace App\Domain\Tenant\Resources;

use App\Domain\Common\Models\Media;
use App\Domain\Common\Resources\MediaResource;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Tenant
 */
class TenantResource extends JsonResource
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
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'vatId'       => $this->vat_id,
            'taxId'       => $this->tax_id,
            'regon'       => $this->regon,
            'email'       => $this->email,
            'phone'       => $this->phone,
            'website'     => $this->website,
            'country'     => $this->country,
            'description' => $this->description,
            'createdAt'   => $this->created_at?->toIso8601String(),
            'updatedAt'   => $this->updated_at?->toIso8601String(),
            'deletedAt'   => $this->deleted_at?->toIso8601String(),
            'logoUrl'     => $logoMedia ? $this->getMediaSignedUrl('logo') : null,
            'logo'        => $logoMedia ? new MediaResource($logoMedia) : null,
            'preferences' => $this->whenLoaded('preferences', fn () => new TenantPreferencesResource($this->preferences)),
        ];
    }
}
