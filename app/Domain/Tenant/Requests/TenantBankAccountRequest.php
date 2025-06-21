<?php

namespace App\Domain\Tenant\Requests;

use App\Http\Requests\BaseFormRequest;

class TenantBankAccountRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'iban'          => ['required', 'string', 'max:50'],
            'swift'         => ['nullable', 'string', 'max:20'],
            'bankName'      => ['nullable', 'string', 'max:255'],
            'currency'      => ['nullable', 'string', 'max:3'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'isDefault'     => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'bankName.required' => 'The bank name is required.',
            'bankName.string'   => 'The bank name must be a string.',
            'bankName.max'      => 'The bank name may not be greater than :max characters.',

            'iban.required' => 'The IBAN is required.',
            'iban.string'   => 'The IBAN must be a string.',
            'iban.max'      => 'The IBAN may not be greater than :max characters.',

            'swift.string'   => 'The SWIFT code must be a string.',
            'swift.max'      => 'The SWIFT code may not be greater than :max characters.',

            'isDefault.boolean' => 'The is default field must be true or false.',
        ];
    }
}
