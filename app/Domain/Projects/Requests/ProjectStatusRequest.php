<?php

namespace App\Domain\Projects\Requests;

use App\Http\Requests\BaseFormRequest;

class ProjectStatusRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'color'     => ['required', 'string', 'max:7'], // hex color
            'sortOrder' => ['sometimes', 'integer'],
            'isDefault' => ['sometimes', 'boolean'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        return [
            'name'       => $validated['name'],
            'color'      => $validated['color'],
            'sort_order' => $validated['sortOrder'] ?? null,
            'is_default' => $validated['isDefault'] ?? false,
        ];
    }
}
