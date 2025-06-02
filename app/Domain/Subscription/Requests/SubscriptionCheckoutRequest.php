<?php

namespace App\Domain\Subscription\Requests;

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
            'planId'     => ['required', 'exists:subscription_plans,id'],
            'successUrl' => ['nullable', 'url'],
            'cancelUrl'  => ['nullable', 'url'],
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
            'planId.required' => 'The plan ID is required.',
            'planId.exists'   => 'The selected plan does not exist.',
            'successUrl.url'  => 'The success URL must be a valid URL.',
            'cancelUrl.url'   => 'The cancel URL must be a valid URL.',
        ];
    }
}
