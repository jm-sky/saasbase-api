<?php

namespace App\Domain\Auth\Requests;

use App\Http\Requests\BaseFormRequest;

class StoreApiKeyRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'scopes'    => ['required', 'array'],
            'scopes.*'  => ['string', 'in:*,read,write'],
            'expiresAt' => ['nullable', 'date', 'after:now'],
        ];
    }
}
