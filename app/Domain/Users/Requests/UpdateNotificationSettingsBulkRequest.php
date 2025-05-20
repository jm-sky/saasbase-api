<?php

namespace App\Domain\Users\Requests;

use App\Http\Requests\BaseFormRequest;

class UpdateNotificationSettingsBulkRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'settings'              => ['required', 'array'],
            'settings.*.channel'    => ['required', 'string', 'max:255'],
            'settings.*.settingKey' => ['required', 'string', 'max:255'],
            'settings.*.enabled'    => ['required', 'boolean'],
        ];
    }
}
