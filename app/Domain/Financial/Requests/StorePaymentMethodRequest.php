<?php

namespace App\Domain\Financial\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Add policy logic if needed
    }

    public function rules(): array
    {
        return [
            'key'         => ['required', 'string', 'max:64', 'unique:payment_methods,key,NULL,id,tenant_id,' . $this->tenant_id],
            'name'        => ['required', 'string', 'max:255'],
            'paymentDays' => ['nullable', 'integer'],
        ];
    }
}
