<?php

namespace App\Domain\Contractors\Requests;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Validator;

class ContractorRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'contractor.tenantId'    => ['required', 'uuid', 'exists:tenants,id'],
            'contractor.name'        => ['required', 'string', 'max:255'],
            'contractor.email'       => ['nullable', 'email', 'max:255'],
            'contractor.phone'       => ['nullable', 'string', 'max:20'],
            'contractor.website'     => ['nullable', 'string', 'max:255'],
            'contractor.country'     => ['nullable', 'string', 'max:100'],
            'contractor.vatId'       => ['nullable', 'string', 'max:20'],
            'contractor.taxId'       => ['nullable', 'string', 'max:20'],
            'contractor.regon'       => ['nullable', 'string', 'max:20'],
            'contractor.description' => ['nullable', 'string'],
            'contractor.isActive'    => ['boolean'],
            'contractor.isBuyer'     => ['boolean'],
            'contractor.isSupplier'  => ['boolean'],

            'address'     => ['nullable', 'sometimes', 'array'],
            'bankAccount' => ['nullable', 'sometimes', 'array'],

            'options.fetchLogo' => ['sometimes', 'nullable', 'boolean'],
        ];

        if ($this->input('address.street') || $this->input('address.city') || $this->input('address.postalCode')) {
            $rules = [
                ...$rules,
                'address.country'    => ['required', 'string', 'max:2'],
                'address.city'       => ['required', 'string', 'max:255'],
                'address.postalCode' => ['required', 'string', 'max:20'],
                'address.street'     => ['nullable', 'string', 'max:255'],
                'address.building'   => ['nullable', 'string', 'max:20'],
                'address.flat'       => ['nullable', 'string', 'max:20'],
            ];
        }

        if ($this->input('bankAccount.iban')) {
            $rules = [
                ...$rules,
                'bankAccount.iban'     => ['required', 'string', 'max:50'],
                'bankAccount.bankName' => ['nullable', 'string', 'max:100'],
                'bankAccount.swift'    => ['nullable', 'string', 'max:20'],
                'bankAccount.currency' => ['nullable', 'string', 'max:20'],
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'contractor.tenantId.required' => 'The tenant ID is required.',
            'contractor.tenantId.uuid'     => 'The tenant ID must be a valid UUID.',
            'contractor.tenantId.exists'   => 'The selected tenant does not exist.',

            'contractor.name.required'  => 'The name field is required.',
            'contractor.email.email'    => 'The email must be a valid email address.',
        ];
    }

    public function prepareForValidation(): void
    {
        $this->mergeTenantId();

        $this->merge([
            'contractor.tenantId' => $this->user()->getTenantId(),
        ]);

        if ($this->input('address.street') || $this->input('address.city') || $this->input('address.postalCode')) {
            $this->merge([
                'address.country' => $this->input('contractor.country'),
            ]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $this->checkTenantId($validator);
    }
}
