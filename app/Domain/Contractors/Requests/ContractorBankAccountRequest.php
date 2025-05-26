<?php

namespace App\Domain\Contractors\Requests;

use App\Http\Requests\BaseFormRequest;

class ContractorBankAccountRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'iban'          => ['nullable', 'string', 'max:50'],
            'swift'         => ['nullable', 'string', 'max:50'],
            'currency'      => ['nullable', 'string', 'max:3'],
            'bankName'      => ['nullable', 'string', 'max:255'],
            'description'   => ['nullable', 'string', 'max:255'],
            'isDefault'     => ['boolean'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->mergeTenantId();

        $this->merge([
            'isDefault' => $this->input('isDefault', false),
        ]);
    }
}
