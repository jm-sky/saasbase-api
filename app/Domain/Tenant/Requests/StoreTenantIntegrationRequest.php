<?php

namespace App\Domain\Tenant\Requests;

use App\Domain\Tenant\Enums\TenantIntegrationMode;
use App\Domain\Tenant\Enums\TenantIntegrationType;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreTenantIntegrationRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'        => ['required', new Enum(TenantIntegrationType::class)],
            'mode'        => ['required', new Enum(TenantIntegrationMode::class)],
            'enabled'     => ['boolean'],
            'credentials' => ['nullable', 'array', 'required_if:mode,custom'],
            'meta'        => ['nullable', 'array'],
        ];
    }

    public function attributes(): array
    {
        return [
            'type'        => 'integration type',
            'mode'        => 'integration mode',
            'credentials' => 'integration credentials',
            'meta'        => 'integration metadata',
        ];
    }
}
