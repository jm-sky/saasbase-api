<?php

namespace App\Domain\EDoreczenia\Requests;

use App\Http\Requests\BaseFormRequest;

class SearchMessageRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'   => ['sometimes', 'string', 'in:pending,sent,failed'],
            'provider' => ['sometimes', 'string'],
            'perPage'  => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort'     => ['sometimes', 'string', 'in:createdAt,updatedAt,-createdAt,-updatedAt'],
        ];
    }
}
