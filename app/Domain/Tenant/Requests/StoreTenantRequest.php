<?php

namespace App\Domain\Tenant\Requests;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Support\Str;

class StoreTenantRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant'             => ['nullable', 'array'],
            'tenant.name'        => ['required', 'string', 'max:255'],
            'tenant.slug'        => ['nullable', 'string', 'max:255', 'unique:tenants,slug'],
            'tenant.vatId'       => ['nullable', 'string', 'max:20'],
            'tenant.taxId'       => ['nullable', 'string', 'max:20'],
            'tenant.regon'       => ['nullable', 'string', 'max:20'],
            'tenant.email'       => ['nullable', 'email', 'max:254'],
            'tenant.phone'       => ['nullable', 'string', 'max:20'],
            'tenant.website'     => ['nullable', 'string', 'max:255'],
            'tenant.country'     => ['nullable', 'string', 'max:2'],
            'tenant.description' => ['nullable', 'string'],

            'address'            => ['nullable', 'array'],
            'address.country'    => ['nullable', 'string', 'max:2'],
            'address.city'       => ['nullable', 'string', 'max:255'],
            'address.postalCode' => ['nullable', 'string', 'max:20'],
            'address.street'     => ['nullable', 'string', 'max:255'],
            'address.building'   => ['nullable', 'string', 'max:20'],
            'address.flat'       => ['nullable', 'string', 'max:20'],

            'bankAccount'           => ['nullable', 'array'],
            'bankAccount.iban'      => ['nullable', 'string', 'max:100'],
            'bankAccount.swift'     => ['nullable', 'string', 'max:50'],
            'bankAccount.bankName'  => ['nullable', 'string', 'max:255'],
            'bankAccount.currency'  => ['nullable', 'string', 'max:3'],
            'bankAccount.country'   => ['nullable', 'string', 'max:2'],
            'bankAccount.isDefault' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'tenant.name.required' => 'The name field is required.',
            'tenant.slug.required' => 'The slug field is required.',
            'tenant.slug.unique'   => 'This slug is already taken.',
            'tenant.email.email'   => 'The email must be a valid email address.',
            'tenant.country.max'   => 'The country must be a 2-letter ISO code.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'tenant.slug' => $this->tenant['slug'] ?? Str::slug($this->tenant['name']),
        ]);
    }
}
