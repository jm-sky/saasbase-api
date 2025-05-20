<?php

namespace App\Domain\Contractors\Requests;

use App\Http\Requests\BaseFormRequest;

class ContractorContactPersonRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['nullable', 'email', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:255'],
            'position'    => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
}
