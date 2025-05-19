<?php

namespace App\Domain\Invoice\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'                  => ['required', 'string'],
            'issue_date'            => ['required', 'date'],
            'status'                => ['required', 'string'],
            'number'                => ['required', 'string'],
            'numbering_template_id' => ['required', 'string', 'exists:numbering_templates,id'],
            'total_net'             => ['required', 'numeric'],
            'total_tax'             => ['required', 'numeric'],
            'total_gross'           => ['required', 'numeric'],
            'currency'              => ['required', 'string', 'size:3'],
            'exchange_rate'         => ['required', 'numeric'],
            'seller'                => ['required', 'array'],
            'buyer'                 => ['required', 'array'],
            'data'                  => ['required', 'array'],
            'payment'               => ['required', 'array'],
            'options'               => ['required', 'array'],
        ];
    }
}
