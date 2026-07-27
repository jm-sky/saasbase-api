<?php

namespace App\Domain\Invoice\Resources;

use App\Http\Requests\BaseFormRequest;

class StoreInvoiceShareTokenRequest extends BaseFormRequest
{
    public function prepareForValidation(): void
    {
        $invoice = $this->route('invoice');

        $this->merge([
            'invoiceId' => is_object($invoice) ? $invoice->id : $invoice,
        ]);
    }

    public function rules(): array
    {
        return [
            'invoiceId' => ['required', 'ulid', 'exists:invoices,id'],
            'expiresAt' => ['required', 'date'],
            'onlyForAuthenticated' => ['required', 'boolean'],
            'maxUsage' => ['required', 'integer', 'min:1'],
        ];
    }
}
