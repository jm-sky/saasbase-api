<?php

namespace App\Domain\Skills\Requests;

use App\Http\Requests\BaseFormRequest;

class SkillCategoryRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        return [
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
        ];
    }
}
