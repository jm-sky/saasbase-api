<?php

namespace App\Domain\Bank\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetBankInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'iban'    => ['required', 'string', 'regex:/^[A-Z]{2}[0-9]{2}[A-Z0-9]{1,30}$/'],
            'country' => ['sometimes', 'string', 'size:2', 'regex:/^[A-Z]{2}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'iban.required' => 'IBAN is required',
            'iban.regex'    => 'Invalid IBAN format',
            'country.size'  => 'Country code must be 2 characters',
            'country.regex' => 'Country code must be uppercase letters',
        ];
    }
}
