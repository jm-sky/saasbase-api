<?php

namespace App\Domain\Invoice\Requests;

use App\Http\Requests\BaseFormRequest;

class InvoiceAttachmentRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxSize = config('domains.invoices.attachments.max_size', 10240); // in kilobytes

        return [
            'file' => ['required', 'file', 'max:' . $maxSize],
        ];
    }
}
