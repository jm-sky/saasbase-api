<?php

namespace App\Domain\Financial\Requests;

use App\Http\Requests\BaseFormRequest;

class AssignGtuToProductRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gtuCode' => ['required', 'exists:gtu_codes,code'],
        ];
    }
}
