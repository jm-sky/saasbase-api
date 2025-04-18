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
        $uniqueRule = 'unique:contractors,email';

        if ($this->contractor) {
            $uniqueRule .= ',' . $this->contractor->id;
        }

        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', $uniqueRule],
            'phone'    => ['nullable', 'string', 'max:20'],
            'address'  => ['nullable', 'string', 'max:255'],
            'city'     => ['nullable', 'string', 'max:100'],
            'state'    => ['nullable', 'string', 'max:100'],
            'zipCode'  => ['nullable', 'string', 'max:20'],
            'country'  => ['nullable', 'string', 'max:100'],
            'taxId'    => ['nullable', 'string', 'max:50'],
            'notes'    => ['nullable', 'string'],
            'isActive' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'The name field is required.',
            'email.required' => 'The email field is required.',
            'email.email'    => 'The email must be a valid email address.',
            'email.unique'   => 'This email is already taken.',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        // Transform camelCase to snake_case for database
        return [
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'phone'     => $validated['phone'] ?? null,
            'address'   => $validated['address'] ?? null,
            'city'      => $validated['city'] ?? null,
            'state'     => $validated['state'] ?? null,
            'zip_code'  => $validated['zipCode'] ?? null,
            'country'   => $validated['country'] ?? null,
            'tax_id'    => $validated['taxId'] ?? null,
            'notes'     => $validated['notes'] ?? null,
            'is_active' => $validated['isActive'] ?? true,
        ];
    }
}
