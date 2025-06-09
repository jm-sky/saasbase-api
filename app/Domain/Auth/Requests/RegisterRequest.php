<?php

namespace App\Domain\Auth\Requests;

use App\Http\Requests\BaseFormRequest;

class RegisterRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'firstName'      => ['required', 'string', 'max:255'],
            'lastName'       => ['required', 'string', 'max:255'],
            'email'          => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'       => ['required', 'string', 'min:8'],
            'description'    => ['nullable', 'string'],
            'birthDate'      => ['nullable', 'date'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'recaptchaToken' => ['required', 'string'],
        ];
    }
}
