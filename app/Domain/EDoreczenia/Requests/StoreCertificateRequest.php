<?php

namespace App\Domain\EDoreczenia\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('edoreczenia.manage_certificates');
    }

    public function rules(): array
    {
        return [
            'certificate' => ['required', 'file', 'mimes:p12,pem', 'max:10240'], // 10MB max
            'password'    => ['required', 'string'],
            'provider'    => ['required', 'string', 'in:edo_post'],
            'fingerprint' => ['required', 'string', 'size:40'], // SHA-1 fingerprint
            'subject_cn'  => ['required', 'string', 'max:255'],
            'valid_from'  => ['required', 'date'],
            'valid_to'    => ['required', 'date', 'after:valid_from'],
        ];
    }
}
