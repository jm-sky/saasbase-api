<?php

namespace App\Domain\Users\Requests;

use App\Http\Requests\BaseFormRequest;

class StoreTableSettingRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entity'    => ['required', 'string', 'max:255'],
            'name'      => ['nullable', 'string', 'max:255'],
            'config'    => ['required', 'array'],
            'isDefault' => ['boolean'],
        ];
    }
}
