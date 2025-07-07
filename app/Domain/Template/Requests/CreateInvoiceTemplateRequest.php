<?php

namespace App\Domain\Template\Requests;

use App\Domain\Template\Enums\TemplateCategory;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CreateInvoiceTemplateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenantId'    => ['required', 'ulid', 'exists:tenants,id'],
            'userId'      => ['nullable', 'ulid', 'exists:users,id'],
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'content'     => ['required', 'string'],
            'category'    => ['required', 'string', Rule::enum(TemplateCategory::class)],
            'previewData' => ['nullable', 'array'],
            'settings'    => ['nullable', 'array'],
            'isActive'    => ['nullable', 'boolean'],
            'isDefault'   => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'tenantId.required' => 'The tenant ID is required.',
            'tenantId.ulid'     => 'The tenant ID must be a valid ULID.',
            'tenantId.exists'   => 'The selected tenant does not exist.',

            'userId.ulid'   => 'The user ID must be a valid ULID.',
            'userId.exists' => 'The selected user does not exist.',

            'name.required' => 'The name field is required.',
            'name.string'   => 'The name must be a string.',
            'name.max'      => 'The name may not be greater than :max characters.',

            'description.string' => 'The description must be a string.',
            'description.max'    => 'The description may not be greater than :max characters.',

            'content.required' => 'The content field is required.',
            'content.string'   => 'The content must be a string.',

            'category.required' => 'The category field is required.',
            'category.string'   => 'The category must be a string.',

            'previewData.array' => 'The preview data must be an array.',
            'settings.array'    => 'The settings must be an array.',

            'isActive.boolean'  => 'The is active field must be true or false.',
            'isDefault.boolean' => 'The is default field must be true or false.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->mergeTenantId();
    }

    public function withValidator(Validator $validator): void
    {
        $this->checkTenantId($validator);
    }
}
