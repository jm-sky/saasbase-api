<?php

namespace App\Domain\Tenant\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendInvitationRequest extends FormRequest
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
