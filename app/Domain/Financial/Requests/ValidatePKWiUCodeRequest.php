<?php

namespace App\Domain\Financial\Requests;

use App\Http\Requests\BaseFormRequest;

class ValidatePKWiUCodeRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'regex:/^[0-9]{2}\.[0-9]{2}\.[0-9]{2}\.[0-9]$/'],
        ];
    }
}
