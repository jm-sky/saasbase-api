<?php

namespace App\Domain\Subscription\Resources;

use App\Domain\Subscription\Models\AddonPurchase;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AddonPurchase
 */
final class AddonPurchaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'addon'     => new AddonResource($this->whenLoaded('package')),
            'quantity'  => $this->quantity,
            'amount'    => $this->amount,
            'currency'  => $this->currency,
            'status'    => $this->status,
            'expiresAt' => $this->expires_at,
            'isActive'  => $this->isActive(),
            'isExpired' => $this->isExpired(),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
