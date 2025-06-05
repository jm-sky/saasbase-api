<?php

namespace App\Domain\Auth\Requests;

use App\Http\Requests\BaseFormRequest;

class ArchiveNotificationsRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids'   => ['required', 'array'],
            'ids.*' => ['required', 'ulid', 'exists:notifications,id'],
        ];
    }
}
