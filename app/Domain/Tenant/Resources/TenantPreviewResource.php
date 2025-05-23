<?php

namespace App\Domain\Tenant\Resources;

use App\Domain\Common\Resources\MediaResource;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantPreviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Tenant $this->resource */
        $logoMedia = $this->getFirstMedia('logo');
        $logoUrl = $this->getMediaSignedUrl('logo');

        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'vatId'       => $this->vat_id,
            'taxId'       => $this->tax_id,
            'regon'       => $this->regon,
            'logoUrl'     => $logoUrl,
            'logo'        => $logoMedia ? new MediaResource($logoMedia) : null,
        ];
    }
}
