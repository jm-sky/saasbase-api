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
            'featureId'          => $this->feature->id,
            'featureName'        => $this->feature->name,
            'featureDescription' => $this->feature->description,
            'featureType'        => $this->feature->type,
            'value'              => $this->value,
            'isUnlimited'        => 'boolean' === $this->feature->type && true === $this->value,
            'isLimited'          => 'integer' === $this->feature->type && 'unlimited' !== $this->value,
        ];
    }
}
