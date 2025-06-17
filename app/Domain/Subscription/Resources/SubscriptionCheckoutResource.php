<?php

namespace App\Domain\Subscription\Resources;

use App\Domain\Subscription\DTOs\CheckoutDataDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CheckoutDataDTO
 */
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
            'checkoutUrl' => $this->checkoutUrl,
            'sessionId'   => $this->sessionId,
        ];
    }
}
