<?php

namespace App\Domain\Exchanges\Requests;

use App\Http\Requests\BaseFormRequest;

class SearchExchangeRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'currency' => ['sometimes', 'string', 'size:3'],
            'perPage'  => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort'     => ['sometimes', 'string', 'in:name,currency,createdAt,updatedAt,-name,-currency,-createdAt,-updatedAt'],
        ];
    }
}
