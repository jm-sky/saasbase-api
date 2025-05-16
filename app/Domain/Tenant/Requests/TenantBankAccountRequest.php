<?php

namespace App\Domain\Tenant\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TenantBankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bankName'      => ['required', 'string', 'max:100'],
            'accountNumber' => ['required', 'string', 'max:34'],
            'swiftCode'     => ['required', 'string', 'max:11'],
            'isDefault'     => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'bankName.required' => 'The bank name is required.',
            'bankName.string'   => 'The bank name must be a string.',
            'bankName.max'      => 'The bank name may not be greater than :max characters.',

            'accountNumber.required' => 'The account number is required.',
            'accountNumber.string'   => 'The account number must be a string.',
            'accountNumber.max'      => 'The account number may not be greater than :max characters.',

            'swiftCode.required' => 'The SWIFT code is required.',
            'swiftCode.string'   => 'The SWIFT code must be a string.',
            'swiftCode.max'      => 'The SWIFT code may not be greater than :max characters.',

            'isDefault.boolean' => 'The is default field must be true or false.',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        return [
            'bank_name'      => $validated['bankName'],
            'account_number' => $validated['accountNumber'],
            'swift_code'     => $validated['swiftCode'],
            'is_default'     => $validated['isDefault'] ?? false,
        ];
    }
}
