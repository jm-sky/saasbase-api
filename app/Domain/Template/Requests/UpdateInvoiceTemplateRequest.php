<?php

namespace App\Domain\Template\Requests;

use App\Domain\Template\Enums\TemplateCategory;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateInvoiceTemplateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'content'     => ['sometimes', 'required', 'string'],
            'category'    => ['sometimes', 'required', 'string', Rule::enum(TemplateCategory::class)],
            'previewData' => ['nullable', 'array'],
            'settings'    => ['nullable', 'array'],
            'isActive'    => ['nullable', 'boolean'],
            'isDefault'   => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
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
}
