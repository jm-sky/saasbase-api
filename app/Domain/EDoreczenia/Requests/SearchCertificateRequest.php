<?php

namespace App\Domain\EDoreczenia\Requests;

use App\Http\Requests\BaseFormRequest;

class SearchCertificateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'   => ['sometimes', 'string', 'in:active,expired,revoked'],
            'provider' => ['sometimes', 'string'],
            'perPage'  => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort'     => ['sometimes', 'string', 'in:validFrom,validTo,createdAt,updatedAt,-validFrom,-validTo,-createdAt,-updatedAt'],
        ];
    }
}
