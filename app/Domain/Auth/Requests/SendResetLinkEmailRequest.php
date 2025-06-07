<?php

namespace App\Domain\Auth\Requests;

use App\Http\Requests\BaseFormRequest;

class SendResetLinkEmailRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'          => ['required', 'email'],
            'recaptchaToken' => ['required', 'string'],
        ];
    }
}
