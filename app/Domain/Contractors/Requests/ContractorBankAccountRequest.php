<?php

namespace App\Domain\Contractors\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContractorBankAccountRequest extends FormRequest
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
        $this->merge([
            'isDefault' => $this->input('isDefault', false),
            'tenantId'  => $this->user()->getTenantId(),
        ]);
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        return [
            'tenant_id'      => $validated['tenantId'] ?? null,
            'iban'           => $validated['iban'] ?? null,
            'swift'          => $validated['swift'] ?? null,
            'currency'       => $validated['currency'] ?? null,
            'bank_name'      => $validated['bankName'] ?? null,
            'description'    => $validated['description'] ?? null,
            'is_default'     => $validated['isDefault'] ?? false,
        ];
    }
}
