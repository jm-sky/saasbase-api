<?php

namespace App\Domain\Tenant\Requests;

use App\Domain\Tenant\Enums\TenantIntegrationMode;
use App\Domain\Tenant\Models\TenantIntegration;
use App\Domain\Tenant\Support\IntegrationAllowedHosts;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $endpoint = $this->input('credentials.endpoint');

            if (!$endpoint) {
                return;
            }

            /** @var ?TenantIntegration $integration */
            $integration = $this->route('integration');
            $type        = $integration?->type?->value;

            if (!$type || !IntegrationAllowedHosts::isAllowed($type, (string) $endpoint)) {
                $validator->errors()->add('credentials.endpoint', 'This endpoint is not allowed for this integration type.');
            }
        });
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
