<?php

namespace App\Domain\Subscription\Requests;

use App\Domain\Subscription\Enums\BillingInterval;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class StoreSubscriptionRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'planId'          => ['required', 'exists:subscription_plans,id'],
            'billingInterval' => ['required', Rule::enum(BillingInterval::class)],
            'paymentMethodId' => ['required', 'string'],
            'trialEndsAt'     => ['nullable', 'date', 'after:now'],
            'couponCode'      => ['nullable', 'string', 'max:50'],
            'metadata'        => ['nullable', 'array'],
            'metadata.*'      => ['string'],
        ];
    }

    public function messages(): array
    {
        return [
            'planId.required'          => 'Please select a subscription plan.',
            'planId.exists'            => 'The selected plan is invalid.',
            'billingInterval.required' => 'Please select a billing interval.',
            'billingInterval.enum'     => 'The selected billing interval is invalid.',
            'paymentMethodId.required' => 'Please provide a payment method.',
            'trialEndsAt.date'         => 'The trial end date must be a valid date.',
            'trialEndsAt.after'        => 'The trial end date must be in the future.',
            'couponCode.max'           => 'The coupon code must not exceed 50 characters.',
        ];
    }
}
