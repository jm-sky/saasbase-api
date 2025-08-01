<?php

namespace App\Domain\Invoice\Resources;

use App\Http\Requests\BaseFormRequest;

class StoreInvoiceShareTokenRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'invoiceId'            => ['required', 'ulid', 'exists:invoices,id'],
            'expiresAt'            => ['required', 'date'],
            'onlyForAuthenticated' => ['required', 'boolean'],
            'maxUsage'             => ['required', 'integer'],
        ];
    }
}
