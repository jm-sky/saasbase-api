<?php

namespace App\Domain\Contractors\Requests;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Validator;

class UpdateContractorRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contractor.tenantId'    => ['nullable', 'ulid', 'exists:tenants,id'],
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

            'options.fetchLogo'      => ['sometimes', 'nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'contractor.tenantId.ulid'     => 'The tenant ID must be a valid ULID.',
            'contractor.tenantId.exists'   => 'The selected tenant does not exist.',
            'contractor.name.required'     => 'The name field is required.',
            'contractor.email.email'       => 'The email must be a valid email address.',
        ];
    }

    public function prepareForValidation(): void
    {
        $this->mergeTenantId();

        $this->merge([
            'contractor.tenantId' => $this->user()->getTenantId(),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $this->checkTenantId($validator);
    }
}
