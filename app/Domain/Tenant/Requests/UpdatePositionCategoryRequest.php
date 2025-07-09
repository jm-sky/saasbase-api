<?php

namespace App\Domain\Tenant\Requests;

use App\Http\Requests\BaseFormRequest;

class UpdatePositionCategoryRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'string', 'max:255'],
            'slug'        => ['sometimes', 'string', 'max:255', 'unique:position_categories,slug,' . $this->route('positionCategory')],
            'description' => ['nullable', 'string', 'max:255'],
            'sortOrder'   => ['nullable', 'integer'],
            'isActive'    => ['sometimes', 'boolean'],
        ];
    }
}
