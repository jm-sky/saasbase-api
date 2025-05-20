<?php

namespace App\Domain\Auth\Requests;

use App\Http\Requests\BaseFormRequest;

class UpdateUserSettingsRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'language'      => ['sometimes', 'string', 'max:10'],
            'theme'         => ['sometimes', 'string', 'in:light,dark,system'],
            'timezone'      => ['sometimes', 'string', 'timezone'],
            'preferences'   => ['sometimes', 'array'],
            'preferences.*' => ['sometimes', 'string'],
        ];
    }
}
