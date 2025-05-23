<?php

namespace App\Domain\Contractors\Requests;

use App\Domain\Auth\Models\User;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class ContractorRequest extends BaseFormRequest
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
            'website'     => ['nullable', 'string', 'max:255'],
            'country'     => ['nullable', 'string', 'max:100'],
            'vatId'       => ['nullable', 'string', 'max:20'],
            'taxId'       => ['nullable', 'string', 'max:20'],
            'regon'       => ['nullable', 'string', 'max:20'],
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
        /** @var User $user */
        $user     = Auth::user();

        $this->merge([
            'tenantId' => $this->input('tenantId') ?? $user->getTenantId(),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var User $user */
            $user     = Auth::user();
            $tenantId = $this->input('tenantId');

            if (!$user->isAdmin() && $tenantId !== $user->getTenantId()) {
                $validator->errors()->add('tenantId', 'You are not allowed to use this tenant ID.');
            }
        });
    }
}
