<?php

namespace App\Domain\Tenant\Resources;

use App\Domain\Common\Resources\MediaResource;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
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

        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'taxId'       => $this->tax_id,
            'email'       => $this->email,
            'phone'       => $this->phone,
            'website'     => $this->website,
            'country'     => $this->country,
            'description' => $this->description,
            'createdAt'   => $this->created_at?->toIso8601String(),
            'updatedAt'   => $this->updated_at?->toIso8601String(),
            'deletedAt'   => $this->deleted_at?->toIso8601String(),
            'logoUrl'     => $logoMedia ? $logoMedia->getUrl() : null,
            'logo'        => $logoMedia ? new MediaResource($logoMedia) : null,
        ];
    }
}
