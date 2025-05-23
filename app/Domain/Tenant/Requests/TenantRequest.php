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
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['required', 'string', 'max:255', $uniqueRule],
            'vatId'       => ['nullable', 'string', 'max:20'],
            'taxId'       => ['nullable', 'string', 'max:20'],
            'regon'       => ['nullable', 'string', 'max:20'],
            'email'       => ['nullable', 'email', 'max:254'],
            'phone'       => ['nullable', 'string', 'max:20'],
            'website'     => ['nullable', 'string', 'max:255'],
            'country'     => ['nullable', 'string', 'max:2'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'slug.required' => 'The slug field is required.',
            'slug.unique'   => 'This slug is already taken.',
            'email.email'   => 'The email must be a valid email address.',
            'country.max'   => 'The country must be a 2-letter ISO code.',
        ];
    }
}
