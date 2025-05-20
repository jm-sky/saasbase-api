<?php

namespace App\Domain\Auth\Requests;

use App\Http\Requests\BaseFormRequest;

class StoreUserPersonalDataRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gender' => ['required', 'in:male,female,prefer_not_to_say'],
            'pesel'  => ['required', 'string', 'max:11'],
        ];
    }
}
