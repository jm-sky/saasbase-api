<?php

namespace App\Domain\Subscription\Resources;

use App\Domain\Billing\Resources\BillingPriceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'description'     => $this->description,
            'stripeProductId' => $this->stripe_product_id,
            'prices'          => BillingPriceResource::collection($this->whenLoaded('prices')),
            'features'        => $this->whenLoaded('planFeatures', function () {
                return PlanFeatureResource::collection($this->planFeatures);
            }),
            'isActive'        => $this->is_active,
            'createdAt'       => $this->created_at,
            'updatedAt'       => $this->updated_at,
        ];
    }
}
