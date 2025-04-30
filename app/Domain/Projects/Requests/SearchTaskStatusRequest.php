<?php

namespace App\Domain\Projects\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchTaskStatusRequest extends FormRequest
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
