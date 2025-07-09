<?php

namespace App\Domain\Financial\Requests;

use App\Http\Requests\BaseFormRequest;

class AssignGtuToInvoiceLineRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gtuCode'          => ['required', 'exists:gtu_codes,code'],
            'assignmentReason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
