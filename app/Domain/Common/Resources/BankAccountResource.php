<?php

namespace App\Domain\Common\Resources;

use App\Domain\Common\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BankAccount
 */
class BankAccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'tenantId'     => $this->tenant_id,
            'bankableId'   => $this->bankable_id,
            'bankableType' => $this->bankable_type,
            'iban'         => $this->iban,
            'country'      => $this->country,
            'swift'        => $this->swift,
            'isDefault'    => $this->is_default,
            'currency'     => $this->currency,
            'bankName'     => $this->bank_name,
            'description'  => $this->description,
            'createdAt'    => $this->created_at?->toIso8601String(),
            'updatedAt'    => $this->updated_at?->toIso8601String(),
        ];
    }
}
