<?php

namespace App\Domain\Products\Resources;

use App\Domain\Common\Resources\MediaResource;
use App\Domain\Products\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Product $this->resource */
        $logoMedia = $this->getFirstMedia('logo');

        return [
            'id'           => $this->id,
            'tenantId'     => $this->tenant_id,
            'name'         => $this->name,
            'description'  => $this->description,
            'priceNet'     => $this->price_net,
            'unitId'       => $this->unit_id,
            'vatRateId'    => $this->vat_rate_id,
            'symbol'       => $this->symbol,
            'ean'          => $this->ean,
            'externalId'   => $this->external_id,
            'sourceSystem' => $this->source_system,
            'createdAt'    => $this->created_at?->toIso8601String(),
            'updatedAt'    => $this->updated_at?->toIso8601String(),
            'deletedAt'    => $this->deleted_at?->toIso8601String(),
            'logo'         => $logoMedia ? new MediaResource($logoMedia) : null,
            'tags'         => method_exists($this->resource, 'getTagNames') ? $this->getTagNames() : [],
            'unit'         => $this->whenLoaded('unit', fn () => new MeasurementUnitResource($this->unit)),
            'vatRate'      => $this->whenLoaded('vatRate', fn () => new VatRateResource($this->vatRate)),
        ];
    }
}
