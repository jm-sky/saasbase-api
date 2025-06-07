<?php

namespace App\Domain\Auth\Requests;

use App\Http\Requests\BaseFormRequest;

class LoginRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'          => ['required', 'email'],
            'password'       => ['required', 'string'],
            'remember'       => ['nullable', 'sometimes', 'boolean'],
            'recaptchaToken' => ['required', 'string'],
        ];
    }
}
