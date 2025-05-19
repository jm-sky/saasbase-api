<?php

namespace App\Domain\Invoice\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'                  => ['sometimes', 'string'],
            'issue_date'            => ['sometimes', 'date'],
            'status'                => ['sometimes', 'string'],
            'number'                => ['sometimes', 'string'],
            'numbering_template_id' => ['sometimes', 'string', 'exists:numbering_templates,id'],
            'total_net'             => ['sometimes', 'numeric'],
            'total_tax'             => ['sometimes', 'numeric'],
            'total_gross'           => ['sometimes', 'numeric'],
            'currency'              => ['sometimes', 'string', 'size:3'],
            'exchange_rate'         => ['sometimes', 'numeric'],
            'seller'                => ['sometimes', 'array'],
            'buyer'                 => ['sometimes', 'array'],
            'data'                  => ['sometimes', 'array'],
            'payment'               => ['sometimes', 'array'],
            'options'               => ['sometimes', 'array'],
        ];
    }
}
