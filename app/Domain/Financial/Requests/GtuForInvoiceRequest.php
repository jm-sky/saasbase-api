<?php

namespace App\Domain\Financial\Requests;

use App\Http\Requests\BaseFormRequest;

class GtuForInvoiceRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoiceId' => ['required', 'exists:invoices,id'],
        ];
    }
}
