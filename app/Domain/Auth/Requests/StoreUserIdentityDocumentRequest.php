<?php

namespace App\Domain\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserIdentityDocumentRequest extends FormRequest
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
            'country'        => ['required', 'string', 'size:2'],
            'issued_at'      => ['nullable', 'date'],
            'expires_at'     => ['required', 'date', 'after:issued_at'],
            'meta'           => ['nullable', 'array'],
            'document_image' => ['required', 'image', 'max:10240'], // 10MB max
        ];
    }
}
