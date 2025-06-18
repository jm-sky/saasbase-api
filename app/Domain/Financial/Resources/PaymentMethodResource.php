<?php

namespace App\Domain\Financial\Resources;

use App\Domain\Financial\Models\PaymentMethod;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PaymentMethod
 */
class PaymentMethodResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'tenantId'    => $this->tenant_id,
            'key'         => $this->key,
            'name'        => $this->name,
            'paymentDays' => $this->payment_days,
            'createdAt'   => $this->created_at?->toIso8601String(),
            'updatedAt'   => $this->updated_at?->toIso8601String(),
        ];
    }
}
