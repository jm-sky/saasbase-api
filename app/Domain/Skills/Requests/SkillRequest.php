<?php

namespace App\Domain\Skills\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SkillRequest extends FormRequest
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

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        // Transform camelCase to snake_case for database
        return [
            'category'    => $validated['category'],
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
        ];
    }
}
