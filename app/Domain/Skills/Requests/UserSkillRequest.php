<?php

namespace App\Domain\Skills\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserSkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'userId'     => ['required', 'uuid', 'exists:users,id'],
            'skillId'    => ['required', 'uuid', 'exists:skills,id'],
            'level'      => ['required', 'integer', 'min:1', 'max:5'],
            'acquiredAt' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'userId.required'  => 'The user ID is required.',
            'userId.uuid'      => 'The user ID must be a valid UUID.',
            'userId.exists'    => 'The selected user does not exist.',
            'skillId.required' => 'The skill ID is required.',
            'skillId.uuid'     => 'The skill ID must be a valid UUID.',
            'skillId.exists'   => 'The selected skill does not exist.',
            'level.required'   => 'The level field is required.',
            'level.integer'    => 'The level must be an integer.',
            'level.min'        => 'The level must be at least 1.',
            'level.max'        => 'The level may not be greater than 5.',
            'acquiredAt.date'  => 'The acquired at must be a valid date.',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        // Keep camelCase for DTO and add snake_case for database
        return [
            'user_id'     => $validated['userId'],
            'skill_id'    => $validated['skillId'],
            'level'       => $validated['level'],
            'acquired_at' => $validated['acquiredAt'] ?? null,
        ];
    }
}
