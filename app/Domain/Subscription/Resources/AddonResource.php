<?php

namespace App\Domain\Subscription\Resources;

use App\Domain\Subscription\Enums\AddonType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'description'  => $this->description,
            'type'         => $this->type,
            'typeLabel'    => AddonType::from($this->type)->label(),
            'price'        => $this->price,
            'currency'     => $this->currency,
            'isRecurring'  => $this->isRecurring(),
            'isOneTime'    => $this->isOneTime(),
            'isUsageBased' => $this->isUsageBased(),
            'features'     => $this->features,
            'createdAt'    => $this->created_at,
            'updatedAt'    => $this->updated_at,
        ];
    }
}
