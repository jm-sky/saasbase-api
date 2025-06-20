<?php

namespace App\Domain\Subscription\Requests;

use App\Domain\Subscription\DTOs\CreateSubscriptionDTO;
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
            'billingCustomerId'         => ['required', 'exists:billing_customers,id'],
            'planId'                    => ['required', 'exists:subscription_plans,id'],
            'billingInterval'           => ['required', Rule::enum(BillingInterval::class)],
            'paymentDetails'            => ['required', 'array'],
            'paymentDetails.cardNumber' => ['required', 'string', 'regex:/^\d{16}$/'],
            'paymentDetails.expiry'     => ['required', 'string', 'regex:/^\d{2}\/\d{2}$/'],
            'paymentDetails.cvc'        => ['required', 'string', 'regex:/^\d{3,4}$/'],
            'paymentDetails.name'       => ['required', 'string', 'max:255'],
            'trialEndsAt'               => ['nullable', 'date', 'after:now'],
            'couponCode'                => ['nullable', 'string', 'max:50'],
            'metadata'                  => ['nullable', 'array'],
            'metadata.*'                => ['string'],
        ];
    }

    public function messages(): array
    {
        return [
            'planId.required'                    => 'Please select a subscription plan.',
            'planId.exists'                      => 'The selected plan is invalid.',
            'billingInterval.required'           => 'Please select a billing interval.',
            'billingInterval.enum'               => 'The selected billing interval is invalid.',
            'paymentDetails.required'            => 'Please provide payment details.',
            'paymentDetails.cardNumber.required' => 'Please provide a card number.',
            'paymentDetails.cardNumber.regex'    => 'Please provide a valid card number.',
            'paymentDetails.expiry.required'     => 'Please provide an expiry date.',
            'paymentDetails.expiry.regex'        => 'Please provide a valid expiry date (MM/YY).',
            'paymentDetails.cvc.required'        => 'Please provide a CVC.',
            'paymentDetails.cvc.regex'           => 'Please provide a valid CVC.',
            'paymentDetails.name.required'       => 'Please provide the name on the card.',
            'paymentDetails.name.max'            => 'The name on the card must not exceed 255 characters.',
            'trialEndsAt.date'                   => 'The trial end date must be a valid date.',
            'trialEndsAt.after'                  => 'The trial end date must be in the future.',
            'couponCode.max'                     => 'The coupon code must not exceed 50 characters.',
        ];
    }

    public function toDto(): CreateSubscriptionDTO
    {
        return CreateSubscriptionDTO::fromArray($this->validated());
    }
}
