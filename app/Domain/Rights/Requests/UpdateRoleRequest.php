<?php

namespace App\Domain\Rights\Requests;

use App\Http\Requests\BaseFormRequest;

class UpdateRoleRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'tenantId' => $this->user()->getTenantId(),
        ]);
    }

    public function rules(): array
    {
        return [
            'name'          => 'required|string|max:255',
            'tenantId'      => 'required|string|exists:tenants,id',
            'permissions'   => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ];
    }
}
