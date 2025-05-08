<?php

namespace App\Domain\Contractors\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ContractorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenantId'    => ['required', 'uuid', 'exists:tenants,id'],
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
            'tenantId.required' => 'The tenant ID is required.',
            'tenantId.uuid'     => 'The tenant ID must be a valid UUID.',
            'tenantId.exists'   => 'The selected tenant does not exist.',

            'name.required'  => 'The name field is required.',
            'email.email'    => 'The email must be a valid email address.',
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'tenantId' => $this->input('tenantId') ?? auth()->user()->getTenantId(),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $user     = auth()->user();
            $tenantId = $this->input('tenantId');

            if (!$user->isAdmin() && $tenantId !== $user->getTenantId()) {
                $validator->errors()->add('tenantId', 'You are not allowed to use this tenant ID.');
            }
        });
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        return [
            'tenant_id'   => $validated['tenantId'],
            'name'        => $validated['name'],
            'email'       => $validated['email'] ?? null,
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
