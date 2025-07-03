<?php

namespace App\Domain\Tenant\Requests;

use App\Http\Requests\BaseFormRequest;

class StoreOrganizationUnitRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['required', 'string', 'max:255', 'unique:organization_units,code'],
            'description' => ['nullable', 'string', 'max:255'],
            'isActive'    => ['required', 'boolean'],
            'parentId'    => ['nullable', 'string', 'exists:organization_units,id'],
        ];
    }
}
