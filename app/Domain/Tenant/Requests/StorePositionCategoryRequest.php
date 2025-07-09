<?php

namespace App\Domain\Tenant\Requests;

use App\Http\Requests\BaseFormRequest;

class StorePositionCategoryRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['required', 'string', 'max:255', 'unique:position_categories,slug'],
            'description' => ['nullable', 'string', 'max:255'],
            'sortOrder'   => ['nullable', 'integer'],
            'isActive'    => ['required', 'boolean'],
        ];
    }
}
