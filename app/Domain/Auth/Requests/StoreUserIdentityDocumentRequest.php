<?php

namespace App\Domain\Auth\Requests;

use App\Http\Requests\BaseFormRequest;

class StoreUserIdentityDocumentRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'           => ['required', 'in:tax_id,passport,id_card'],
            'number'         => ['required', 'string'],
            'country'        => ['required', 'string', 'size:2', 'exists:countries,code'],
            'issued_at'      => ['nullable', 'date'],
            'expires_at'     => ['required', 'date', 'after:issued_at'],
            'meta'           => ['nullable', 'array'],
            'document_image' => ['required', 'image', 'max:10240'], // 10MB max
        ];
    }
}
