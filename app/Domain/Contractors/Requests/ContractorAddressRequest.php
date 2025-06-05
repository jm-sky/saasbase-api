<?php

namespace App\Domain\Contractors\Requests;

use App\Domain\Common\Enums\AddressType;
use App\Http\Requests\BaseFormRequest;

class ContractorAddressRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'street'      => ['nullable', 'string', 'max:255'],
            'city'        => ['required', 'string', 'max:255'],
            'postalCode'  => ['nullable', 'string', 'max:20'],
            'country'     => ['required', 'string', 'max:2'],
            'tenantId'    => ['required', 'ulid', 'exists:tenants,id'],
            'building'    => ['nullable', 'string', 'max:255'],
            'flat'        => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type'        => ['required', 'string', 'in:' . implode(',', array_column(AddressType::cases(), 'value'))],
            'isDefault'   => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->mergeTenantId();

        $this->merge([
            'isDefault' => $this->input('isDefault', false),
        ]);
    }
}
