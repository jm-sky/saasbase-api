<?php

namespace App\Domain\Billing\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillingPriceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'stripePriceId' => $this->stripe_price_id,
            'billingPeriod' => $this->billing_period,
            'price'         => $this->price,
            'currency'      => $this->currency,
            'isActive'      => $this->is_active,
            'createdAt'     => $this->created_at,
            'updatedAt'     => $this->updated_at,
        ];
    }
}
