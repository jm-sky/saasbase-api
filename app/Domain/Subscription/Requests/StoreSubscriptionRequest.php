<?php

namespace App\Domain\Subscription\Requests;

use App\Domain\Auth\Models\User;
use App\Domain\Subscription\DTOs\CreateSubscriptionDTO;
use App\Domain\Subscription\Enums\BillingInterval;
use App\Domain\Subscription\Models\BillingCustomer;
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
        /** @var User $user */
        $user = $this->user();

        return [
            'billingCustomerId' => [
                'required',
                Rule::exists('billing_customers', 'id')->where(
                    fn ($query) => $query->whereIn('id', BillingCustomer::query()->forUser($user)->select('id'))
                ),
            ],
            'planId'                    => ['required', 'exists:subscription_plans,id'],
            'billingInterval'           => ['required', Rule::enum(BillingInterval::class)],
            'paymentDetails'                  => ['required', 'array'],
            'paymentDetails.paymentMethodId'  => ['required', 'string', 'regex:/^pm_[a-zA-Z0-9_]+$/'],
            'paymentDetails.name'             => ['required', 'string', 'max:255'],
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
            'paymentDetails.required'                 => 'Please provide payment details.',
            'paymentDetails.paymentMethodId.required' => 'Please provide a payment method.',
            'paymentDetails.paymentMethodId.regex'    => 'Please provide a valid payment method.',
            'paymentDetails.name.required'            => 'Please provide the name on the card.',
            'paymentDetails.name.max'                 => 'The name on the card must not exceed 255 characters.',
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
