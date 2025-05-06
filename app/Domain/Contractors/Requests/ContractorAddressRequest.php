<?php

namespace App\Domain\Contractors\Requests;

use App\Domain\Common\Enums\AddressType;
use Illuminate\Foundation\Http\FormRequest;

class ContractorAddressRequest extends FormRequest
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
            'state'       => ['required', 'string', 'max:255'],
            'postalCode'  => ['nullable', 'string', 'max:20'],
            'country'     => ['required', 'string', 'max:2'],
            'tenantId'    => ['required', 'uuid', 'exists:tenants,id'],
            'building'    => ['nullable', 'string', 'max:255'],
            'flat'        => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type'        => ['required', 'string', 'in:' . implode(',', array_column(AddressType::cases(), 'value'))],
            'isDefault'   => ['boolean'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        return [
            'street'      => $validated['street'] ?? null,
            'city'        => $validated['city'],
            'state'       => $validated['state'],
            'postal_code' => $validated['postalCode'] ?? null,
            'country'     => $validated['country'],
            'tenant_id'   => $validated['tenantId'],
            'building'    => $validated['building'] ?? null,
            'flat'        => $validated['flat'] ?? null,
            'description' => $validated['description'] ?? null,
            'type'        => $validated['type'],
            'is_default'  => $validated['isDefault'] ?? false,
        ];
    }
}
