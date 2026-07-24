<?php

namespace App\Domain\Financial\Resources;

use App\Domain\Financial\Models\GtuCode;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin GtuCode
 */
class GtuCodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'amountThresholdPln' => $this->amount_threshold_pln,
            'applicableConditions' => $this->applicable_conditions,
            'isActive' => $this->is_active,
            'effectiveFrom' => $this->effective_from?->toIso8601String(),
            'effectiveTo' => $this->effective_to?->toIso8601String(),
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}
