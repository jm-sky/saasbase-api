<?php

namespace App\Domain\Users\Requests;

use App\Http\Requests\BaseFormRequest;

class UpdateTableSettingRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => ['nullable', 'string', 'max:255'],
            'config'    => ['required', 'array'],
            'isDefault' => ['boolean'],
        ];
    }
}
