<?php

namespace App\Domain\Tenant\Requests;

use App\Domain\Common\Enums\AddressType;
use Illuminate\Foundation\Http\FormRequest;

class TenantAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'country'     => ['required', 'string', 'max:2'],
            'postalCode'  => ['nullable', 'string', 'max:10'],
            'city'        => ['required', 'string', 'max:100'],
            'street'      => ['nullable', 'string', 'max:100'],
            'building'    => ['nullable', 'string', 'max:20'],
            'flat'        => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:255'],
            'type'        => ['required', 'string', 'in:' . implode(',', array_column(AddressType::cases(), 'value'))],
            'isDefault'   => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'country.required' => 'The country is required.',
            'country.string'   => 'The country must be a string.',
            'country.max'      => 'The country may not be greater than :max characters.',

            'postalCode.string' => 'The postal code must be a string.',
            'postalCode.max'    => 'The postal code may not be greater than :max characters.',

            'city.required' => 'The city is required.',
            'city.string'   => 'The city must be a string.',
            'city.max'      => 'The city may not be greater than :max characters.',

            'street.string' => 'The street must be a string.',
            'street.max'    => 'The street may not be greater than :max characters.',

            'building.string' => 'The building must be a string.',
            'building.max'    => 'The building may not be greater than :max characters.',

            'flat.string' => 'The flat must be a string.',
            'flat.max'    => 'The flat may not be greater than :max characters.',

            'description.string' => 'The description must be a string.',
            'description.max'    => 'The description may not be greater than :max characters.',

            'type.required' => 'The address type is required.',
            'type.string'   => 'The address type must be a string.',
            'type.in'       => 'The selected address type is invalid.',

            'isDefault.boolean' => 'The is default field must be true or false.',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        return [
            'country'     => $validated['country'],
            'postal_code' => $validated['postalCode'] ?? null,
            'city'        => $validated['city'],
            'street'      => $validated['street'] ?? null,
            'building'    => $validated['building'] ?? null,
            'flat'        => $validated['flat'] ?? null,
            'description' => $validated['description'] ?? null,
            'type'        => $validated['type'],
            'is_default'  => $validated['isDefault'] ?? false,
        ];
    }
}
