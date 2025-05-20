<?php

namespace App\Domain\Users\Requests;

use App\Http\Requests\BaseFormRequest;

class UpdateProfilePrivacyRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'isProfilePublic'         => ['boolean'],
            'fieldVisibility'         => ['nullable', 'array'],
            'fieldVisibility.*'       => ['string', 'in:hidden,tenant,public'],
            'visibilityPerTenant'     => ['nullable', 'array'],
            'visibilityPerTenant.*'   => ['array'],
            'visibilityPerTenant.*.*' => ['string', 'in:hidden,tenant,public'],
        ];
    }
}
