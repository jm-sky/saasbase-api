<?php

namespace App\Domain\Skills\Requests;

use App\Http\Requests\BaseFormRequest;

class SkillRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category'    => ['required', 'string', 'exists:skill_categories,name'],
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => 'The category is required.',
            'category.exists'   => 'The selected category does not exist.',
            'name.required'     => 'The name field is required.',
        ];
    }
}
