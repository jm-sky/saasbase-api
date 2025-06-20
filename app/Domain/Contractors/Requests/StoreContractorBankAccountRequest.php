<?php

namespace App\Domain\Contractors\Requests;

use App\Domain\Common\DTOs\BankAccountDTO;
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
            'iban'          => ['required', 'string', 'max:50'],
            'swift'         => ['nullable', 'string', 'max:20'],
            'bankName'      => ['nullable', 'string', 'max:255'],
            'currency'      => ['nullable', 'string', 'max:3'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'isDefault'     => ['sometimes', 'boolean'],
        ];
    }

    public function toBankAccountDto(): BankAccountDTO
    {
        $validated = $this->validated();

        return BankAccountDTO::fromArray($validated);
    }
}
