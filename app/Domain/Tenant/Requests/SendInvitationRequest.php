<?php

namespace App\Domain\Tenant\Requests;

use App\Http\Requests\BaseFormRequest;

class SendInvitationRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        // Authorization handled in controller/policy
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'role'  => ['required', 'string', 'max:64'],
        ];
    }
}
