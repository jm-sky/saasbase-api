<?php

namespace App\Domain\Products\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Remove tenantId rule
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'unitId'      => ['required', 'uuid', 'exists:measurement_units,id'],
            'priceNet'    => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:999999.99'],
            'vatRateId'   => ['required', 'uuid', 'exists:vat_rates,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.string'   => 'The name must be a string.',
            'name.max'      => 'The name may not be greater than :max characters.',

            'description.string' => 'The description must be a string.',
            'description.max'    => 'The description may not be greater than :max characters.',

            'unitId.required' => 'The unit ID is required.',
            'unitId.uuid'     => 'The unit ID must be a valid UUID.',
            'unitId.exists'   => 'The selected unit does not exist.',

            'priceNet.required' => 'The net price is required.',
            'priceNet.numeric'  => 'The net price must be a number.',
            'priceNet.decimal'  => 'The net price must have at most 2 decimal places.',
            'priceNet.min'      => 'The net price must be at least :min.',
            'priceNet.max'      => 'The net price may not be greater than :max.',

            'vatRateId.required' => 'The VAT rate ID is required.',
            'vatRateId.uuid'     => 'The VAT rate ID must be a valid UUID.',
            'vatRateId.exists'   => 'The selected VAT rate does not exist.',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        // Add tenant_id automatically from the authenticated user
        return [
            'tenant_id'   => auth()->user()->getTenantId(),
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'unit_id'     => $validated['unitId'],
            'price_net'   => $validated['priceNet'],
            'vat_rate_id' => $validated['vatRateId'],
         ];
    }
}
