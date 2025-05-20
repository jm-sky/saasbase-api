<?php

namespace App\Domain\Users\Requests;

use App\Http\Requests\BaseFormRequest;

class UpdatePreferenceRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'language'                => ['nullable', 'string', 'size:2'],
            'decimalSeparator'        => ['nullable', 'string', 'size:1'],
            'dateFormat'              => ['nullable', 'string', 'max:20'],
            'darkMode'                => ['nullable', 'string', 'in:system,dark,light'],
            'isSoundEnabled'          => ['nullable', 'boolean'],
            'isProfilePublic'         => ['boolean'],
            'fieldVisibility'         => ['nullable', 'array'],
            'fieldVisibility.*'       => ['string', 'in:hidden,tenant,public'],
            'visibilityPerTenant'     => ['nullable', 'array'],
            'visibilityPerTenant.*'   => ['array'],
            'visibilityPerTenant.*.*' => ['string', 'in:hidden,tenant,public'],
        ];
    }
}
