<?php

namespace App\Domain\Contractors\Resources;

use App\Domain\Contractors\Models\ContractorPreferences;
use App\Domain\Exchanges\Resources\CurrencyResource;
use App\Domain\Financial\Resources\PaymentMethodResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ContractorPreferences
 */
class ContractorPreferencesResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                     => $this->id,
            'defaultPaymentMethodId' => $this->default_payment_method_id,
            'defaultCurrencyCode'    => $this->default_currency_code,
            'defaultLanguage'        => $this->default_language,
            'defaultPaymentDays'     => $this->default_payment_days,
            'defaultTags'            => $this->default_tags,
            'defaultCurrency'        => $this->whenLoaded('defaultCurrency', function () {
                return new CurrencyResource($this->defaultCurrency);
            }),
            'defaultPaymentMethod'   => $this->whenLoaded('defaultPaymentMethod', function () {
                return new PaymentMethodResource($this->defaultPaymentMethod);
            }),
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}
