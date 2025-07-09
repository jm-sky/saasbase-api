<?php

namespace App\Domain\Financial\Requests;

use App\Http\Requests\BaseFormRequest;

class UpdateGtuCodeRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Add your validation rules here
        ];
    }
}
