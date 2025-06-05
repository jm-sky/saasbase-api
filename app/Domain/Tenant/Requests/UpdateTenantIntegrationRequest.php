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
            'mode'        => ['string', 'in:shared,custom'],
            'credentials' => ['nullable', 'array'],
            'meta'        => ['nullable', 'array'],
        ];
    }

    public function attributes(): array
    {
        return [
            'mode'        => 'integration mode',
            'credentials' => 'integration credentials',
            'meta'        => 'integration metadata',
        ];
    }
}
