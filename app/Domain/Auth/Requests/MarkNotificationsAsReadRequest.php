<?php

namespace App\Domain\Auth\Requests;

use App\Http\Requests\BaseFormRequest;

class MarkNotificationsAsReadRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids'   => ['required', 'array'],
            'ids.*' => ['required', 'uuid', 'exists:notifications,id'],
        ];
    }
}
