<?php

namespace App\Domain\Products\Resources;

use App\Domain\Products\Models\VatRate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'id'        => $this->id,
            'name'      => $this->name,
            'rate'      => $this->rate,
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}
