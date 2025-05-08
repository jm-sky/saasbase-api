<?php

namespace App\Domain\Auth\Requests;

use App\Http\Requests\BaseFormRequest;

class UpdateUserProfileRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'firstName'   => ['required', 'string', 'min:2', 'max:50'],
            'lastName'    => ['required', 'string', 'min:2', 'max:50'],
            'description' => ['nullable', 'string', 'min:2', 'max:255'],
            'birthDate'   => ['nullable', 'date'],
            'phone'       => ['nullable', 'string', 'min:2', 'max:20'],
        ];
    }
}
