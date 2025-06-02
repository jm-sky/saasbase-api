<?php

namespace App\Domain\Subscription\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseAddonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'addonId'         => ['required', 'exists:addon_packages,id'],
            'quantity'        => ['required', 'integer', 'min:1'],
            'paymentMethodId' => ['required', 'string'],
            'metadata'        => ['nullable', 'array'],
            'metadata.*'      => ['string'],
        ];
    }

    public function messages(): array
    {
        return [
            'addonId.required'         => 'Please select an addon package.',
            'addonId.exists'           => 'The selected addon package is invalid.',
            'quantity.required'        => 'Please specify the quantity.',
            'quantity.integer'         => 'The quantity must be a whole number.',
            'quantity.min'             => 'The quantity must be at least 1.',
            'paymentMethodId.required' => 'Please provide a payment method.',
        ];
    }
}
