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
            'tenantId' => ['required', 'string', 'exists:tenants,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'unitId' => ['required', 'string', 'exists:units,id'],
            'priceNet' => ['required', 'numeric', 'min:0'],
            'vatRateId' => ['required', 'string', 'exists:vat_rates,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'tenantId.required' => 'The tenant ID is required.',
            'tenantId.exists' => 'The selected tenant does not exist.',
            'name.required' => 'The name field is required.',
            'unitId.required' => 'The unit ID is required.',
            'unitId.exists' => 'The selected unit does not exist.',
            'priceNet.required' => 'The net price is required.',
            'priceNet.numeric' => 'The net price must be a number.',
            'priceNet.min' => 'The net price must be at least 0.',
            'vatRateId.required' => 'The VAT rate ID is required.',
            'vatRateId.exists' => 'The selected VAT rate does not exist.',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        // Transform camelCase to snake_case for database
        return [
            'tenant_id' => $validated['tenantId'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'unit_id' => $validated['unitId'],
            'price_net' => $validated['priceNet'],
            'vat_rate_id' => $validated['vatRateId'],
        ];
    }
}
