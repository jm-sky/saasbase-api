<?php

namespace App\Domain\Subscription\Requests;

use App\Domain\Subscription\Enums\BillingInterval;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateSubscriptionRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'planId'            => ['sometimes', 'exists:subscription_plans,id'],
            'billingInterval'   => ['sometimes', Rule::enum(BillingInterval::class)],
            'paymentMethodId'   => ['sometimes', 'string'],
            'prorate'           => ['boolean'],
            'cancelAtPeriodEnd' => ['boolean'],
            'metadata'          => ['nullable', 'array'],
            'metadata.*'        => ['string'],
        ];
    }

    public function messages(): array
    {
        return [
            'planId.exists'             => 'The selected plan is invalid.',
            'billingInterval.enum'      => 'The selected billing interval is invalid.',
            'paymentMethodId.string'    => 'The payment method ID must be a string.',
            'prorate.boolean'           => 'The prorate field must be true or false.',
            'cancelAtPeriodEnd.boolean' => 'The cancel at period end field must be true or false.',
        ];
    }
}
