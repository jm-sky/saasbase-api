<?php

namespace App\Domain\Financial\Requests;

use App\Http\Requests\BaseFormRequest;

class StoreGtuCodeRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenantId' => ['required', 'string', 'ulid'],
            // Add your validation rules here
        ];
    }
}
