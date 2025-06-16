<?php

namespace App\Domain\Tenant\Requests;

use App\Http\Requests\BaseFormRequest;

class UpdateTenantIntegrationRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'enabled'     => ['boolean'],
            'credentials' => ['nullable', 'array'],
            'meta'        => ['nullable', 'array'],
        ];
    }

    public function attributes(): array
    {
        return [
            'credentials' => 'integration credentials',
            'meta'        => 'integration metadata',
        ];
    }
}
