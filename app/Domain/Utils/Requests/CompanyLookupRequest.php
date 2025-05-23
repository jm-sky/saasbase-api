<?php

namespace App\Domain\Utils\Requests;

use App\Http\Requests\BaseFormRequest;

class CompanyLookupRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vatId'   => ['required', 'string'],
            'country' => ['required', 'string', 'size:2'],
            'force'   => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'vatId.required'   => 'The VAT ID is required.',
            'country.required' => 'The country code is required.',
            'country.size'     => 'The country code must be 2 characters.',
        ];
    }
}
