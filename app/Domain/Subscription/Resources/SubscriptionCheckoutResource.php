<?php

namespace App\Domain\Subscription\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionCheckoutResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'checkoutUrl' => $this->resource['url'],
            'sessionId'   => $this->resource['sessionId'],
        ];
    }
}
