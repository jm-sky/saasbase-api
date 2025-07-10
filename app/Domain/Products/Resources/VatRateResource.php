<?php

namespace App\Domain\Products\Resources;

use App\Domain\Financial\Models\VatRate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin VatRate
 */
class VatRateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var VatRate $this->resource */
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'rate'        => $this->rate,
            'type'        => $this->type->value,
            'countryCode' => $this->country_code,
            'active'      => $this->active,
            'validFrom'   => $this->valid_from?->toIso8601String(),
            'validTo'     => $this->valid_to?->toIso8601String(),
        ];
    }
}
