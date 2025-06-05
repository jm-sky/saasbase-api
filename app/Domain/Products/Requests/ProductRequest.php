<?php

namespace App\Domain\Products\Requests;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Validator;

class ProductRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenantId'    => ['required', 'ulid', 'exists:tenants,id'],
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'unitId'      => ['nullable', 'ulid', 'exists:measurement_units,id'],
            'priceNet'    => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:999999.99'],
            'vatRateId'   => ['nullable', 'ulid', 'exists:vat_rates,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'tenantId.required' => 'The tenant ID is required.',
            'tenantId.ulid'     => 'The tenant ID must be a valid ULID.',
            'tenantId.exists'   => 'The selected tenant does not exist.',

            'name.required' => 'The name field is required.',
            'name.string'   => 'The name must be a string.',
            'name.max'      => 'The name may not be greater than :max characters.',

            'description.string' => 'The description must be a string.',
            'description.max'    => 'The description may not be greater than :max characters.',

            'unitId.required' => 'The unit ID is required.',
            'unitId.ulid'     => 'The unit ID must be a valid ULID.',
            'unitId.exists'   => 'The selected unit does not exist.',

            'priceNet.required' => 'The net price is required.',
            'priceNet.numeric'  => 'The net price must be a number.',
            'priceNet.decimal'  => 'The net price must have at most 2 decimal places.',
            'priceNet.min'      => 'The net price must be at least :min.',
            'priceNet.max'      => 'The net price may not be greater than :max.',

            'vatRateId.required' => 'The VAT rate ID is required.',
            'vatRateId.ulid'     => 'The VAT rate ID must be a valid ULID.',
            'vatRateId.exists'   => 'The selected VAT rate does not exist.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->mergeTenantId();
    }

    public function withValidator(Validator $validator): void
    {
        $this->checkTenantId($validator);
    }
}
