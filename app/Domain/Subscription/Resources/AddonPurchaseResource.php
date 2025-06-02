<?php

namespace App\Domain\Subscription\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddonPurchaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'addon'     => new AddonResource($this->whenLoaded('addon')),
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
