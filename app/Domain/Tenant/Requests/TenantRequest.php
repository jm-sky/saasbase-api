<?php

namespace App\Domain\Tenant\Requests;

use App\Http\Requests\BaseFormRequest;

class TenantRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $uniqueRule = 'unique:tenants,slug';

        if ($this->tenant) {
            $uniqueRule .= ',' . $this->tenant->id;
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', $uniqueRule],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'slug.required' => 'The slug field is required.',
            'slug.unique'   => 'This slug is already taken.',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        return parent::validated();
    }
}
