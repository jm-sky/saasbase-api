<?php

namespace App\Domain\Tenant\Requests;

use App\Domain\Tenant\Enums\TenantIntegrationMode;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateTenantIntegrationRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mode'        => ['sometimes', new Enum(TenantIntegrationMode::class)],
            'enabled'     => ['boolean'],
            'credentials' => ['nullable', 'array', 'required_if:mode,custom'],
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
