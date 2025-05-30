<?php

namespace App\Domain\EDoreczenia\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('edoreczenia.manage_certificates');
    }

    public function rules(): array
    {
        return [
            'is_valid' => ['sometimes', 'boolean'],
        ];
    }
}
