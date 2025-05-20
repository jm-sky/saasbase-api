<?php

namespace App\Domain\Contractors\Requests;

use App\Http\Requests\BaseFormRequest;

class StoreContractorBankAccountRequest extends BaseFormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'bankName'      => ['required', 'string', 'max:255'],
            'accountNumber' => ['required', 'string', 'max:50'],
            'swiftCode'     => ['nullable', 'string', 'max:20'],
            'iban'          => ['nullable', 'string', 'max:50'],
            'currency'      => ['required', 'string', 'max:3'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'isDefault'     => ['boolean'],
        ];
    }
}
