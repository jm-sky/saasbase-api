<?php

namespace App\Domain\Tenant\Requests;

use App\Domain\Rights\Enums\RoleName;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class SendInvitationRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        // Role-level authorization (Owner/Admin only) handled in the controller.
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'role'  => ['required', 'string', Rule::in(array_column(RoleName::cases(), 'value'))],
        ];
    }
}
