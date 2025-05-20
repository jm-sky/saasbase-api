<?php

namespace App\Domain\Auth\Requests;

use App\Http\Requests\BaseFormRequest;

class UpdateUserLanguageRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'language' => ['required', 'string', 'max:10'],
        ];
    }
}
