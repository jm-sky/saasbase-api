<?php

namespace App\Domain\Subscription\Requests;

use App\Domain\Billing\Models\BillingPrice;
use App\Domain\Subscription\Models\SubscriptionPlan;
use App\Http\Requests\BaseFormRequest;

class SubscriptionCheckoutRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'billableType' => ['required', 'in:tenant,user'],
            'planId'       => ['required', 'exists:subscription_plans,id'],
            'priceId'      => [
                'required',
                'exists:billing_prices,id',
                function ($attribute, $value, $fail) {
                    $plan = SubscriptionPlan::find($this->planId);

                    if (!$plan || !$plan->prices()->where('id', $value)->exists()) {
                        $fail('The selected price does not belong to the specified plan.');
                    }
                },
            ],
            'successUrl'   => ['nullable', 'url'],
            'cancelUrl'    => ['nullable', 'url'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'planId.required'  => 'The plan ID is required.',
            'planId.exists'    => 'The selected plan does not exist.',
            'priceId.required' => 'The price ID is required.',
            'priceId.exists'   => 'The selected price does not exist.',
            'successUrl.url'   => 'The success URL must be a valid URL.',
            'cancelUrl.url'    => 'The cancel URL must be a valid URL.',
        ];
    }

    public function getPrice(): BillingPrice
    {
        return BillingPrice::findOrFail($this->priceId);
    }

    public function getPlan(): SubscriptionPlan
    {
        return SubscriptionPlan::findOrFail($this->planId);
    }
}
