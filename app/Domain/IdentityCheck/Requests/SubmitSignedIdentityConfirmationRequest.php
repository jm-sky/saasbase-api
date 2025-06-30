<?php

namespace App\Domain\IdentityCheck\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitSignedIdentityConfirmationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Add authorization logic if needed
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:xml,application/xml,text/xml',
        ];
    }
}
