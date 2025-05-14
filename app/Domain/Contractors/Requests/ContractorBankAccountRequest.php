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
            'bankName'      => ['required', 'string', 'max:255'],
            'accountNumber' => ['required', 'string', 'max:255'],
            'swift'         => ['nullable', 'string', 'max:255'],
            'iban'          => ['nullable', 'string', 'max:255'],
            'isDefault'     => ['boolean'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        return [
            'bank_name'      => $validated['bankName'],
            'account_number' => $validated['accountNumber'],
            'swift'          => $validated['swift'] ?? null,
            'iban'           => $validated['iban'] ?? null,
            'is_default'     => $validated['isDefault'] ?? false,
        ];
    }
}
