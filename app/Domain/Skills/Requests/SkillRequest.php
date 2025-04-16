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
            'categoryId' => ['required', 'string', 'exists:skill_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'categoryId.required' => 'The category ID is required.',
            'categoryId.exists' => 'The selected category does not exist.',
            'name.required' => 'The name field is required.',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        // Transform camelCase to snake_case for database
        return [
            'category_id' => $validated['categoryId'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ];
    }
}
