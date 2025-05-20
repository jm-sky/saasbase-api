<?php

namespace App\Domain\Projects\Requests;

use App\Http\Requests\BaseFormRequest;

class SearchProjectStatusRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'filter.name'      => ['sometimes', 'string'],
            'filter.isDefault' => ['sometimes', 'boolean'],
            'filter.createdAt' => ['sometimes', 'array'],
            'filter.updatedAt' => ['sometimes', 'array'],
            'sort'             => ['sometimes', 'string'],
            'per_page'         => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
