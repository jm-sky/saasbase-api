<?php

namespace App\Domain\Contractors\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContractorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['nullable', 'email', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:20'],
            'country'     => ['nullable', 'string', 'max:100'],
            'taxId'       => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'isActive'    => ['boolean'],
            'isBuyer'     => ['boolean'],
            'isSupplier'  => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'The name field is required.',
            'email.email'    => 'The email must be a valid email address.',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        return [
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'phone'       => $validated['phone'] ?? null,
            'country'     => $validated['country'] ?? null,
            'tax_id'      => $validated['taxId'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active'   => $validated['isActive'] ?? true,
            'is_buyer'    => $validated['isBuyer'] ?? false,
            'is_supplier' => $validated['isSupplier'] ?? false,
        ];
    }
}
