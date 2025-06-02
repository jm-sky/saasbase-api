<?php

namespace App\Domain\Subscription\Resources;

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
            'price'           => $this->price,
            'billingInterval' => $this->interval,
            'stripeProductId' => $this->stripe_product_id,
            'stripePriceId'   => $this->stripe_price_id,
            'features'        => $this->whenLoaded('planFeatures', function () {
                return PlanFeatureResource::collection($this->planFeatures);
            }),
            'currency'  => 'PLN',
            'isCurrent' => false,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
