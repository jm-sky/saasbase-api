<?php

namespace App\Domain\Tenant\Requests;

use App\Http\Requests\BaseFormRequest;

class StoreTenantIntegrationRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'        => ['required', 'string'],
            'enabled'     => ['boolean'],
            'credentials' => ['nullable', 'array'],
            'meta'        => ['nullable', 'array'],
        ];
    }

    public function attributes(): array
    {
        return [
            'type'        => 'integration type',
            'credentials' => 'integration credentials',
            'meta'        => 'integration metadata',
        ];
    }
}
