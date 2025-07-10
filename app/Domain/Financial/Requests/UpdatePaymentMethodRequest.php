<?php

namespace App\Domain\Financial\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Add policy logic if needed
    }

    public function rules(): array
    {
        $id = $this->route('id') ?? $this->route('payment_method');

        return [
            'code'        => ['sometimes', 'required', 'string', 'max:64', 'unique:payment_methods,code,' . $id . ',id,tenant_id,' . $this->tenant_id],
            'name'        => ['sometimes', 'required', 'string', 'max:255'],
            'paymentDays' => ['nullable', 'integer'],
        ];
    }
}
