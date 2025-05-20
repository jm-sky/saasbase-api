<?php

namespace App\Domain\Products\Resources;

use App\Domain\Products\Models\MeasurementUnit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeasurementUnitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var MeasurementUnit $this->resource */
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'symbol'    => $this->symbol,
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}
