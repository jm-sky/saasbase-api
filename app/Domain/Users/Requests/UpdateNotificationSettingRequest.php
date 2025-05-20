<?php

namespace App\Domain\Users\Requests;

use App\Http\Requests\BaseFormRequest;

class UpdateNotificationSettingRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'channel'    => ['required', 'string', 'max:255'],
            'settingKey' => ['required', 'string', 'max:255'],
            'enabled'    => ['required', 'boolean'],
        ];
    }
}
