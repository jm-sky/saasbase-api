<?php

namespace App\Domain\Subscription\Resources;

use App\Domain\Subscription\Models\PlanFeature;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PlanFeature
 */
final class PlanFeatureResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->feature->id,
            'name' => $this->feature->name,
            'description' => $this->feature->description,
            'type' => $this->feature->type,
            'value' => $this->value,
            'defaultValue' => $this->feature->default_value,
            'isUnlimited' => $this->feature->type === 'boolean' && $this->value === true,
            'isLimited' => $this->feature->type === 'integer' && $this->value !== 'unlimited',
        ];
    }
}
