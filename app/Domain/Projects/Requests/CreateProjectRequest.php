<?php

namespace App\Domain\Projects\Requests;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Validator;

class CreateProjectRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'tenantId' => ['required', 'ulid', 'exists:tenants,id'],
            'ownerId' => ['required', 'ulid', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'statusId' => ['required', 'ulid', 'exists:project_statuses,id'],
            'startDate' => ['nullable', 'date'],
            'endDate' => ['nullable', 'date'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->mergeTenantId();

        $this->merge([
            'ownerId' => $this->input('ownerId') ?? $this->user()->id,
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $this->checkTenantId($validator);
    }
}
