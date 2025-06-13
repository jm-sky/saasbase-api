<?php

namespace App\Domain\Expense\Requests;

use App\Domain\Financial\Enums\InvoiceType;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateExpenseRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'                  => ['sometimes', new Enum(InvoiceType::class)],
            'issue_date'            => ['sometimes', 'date'],
            'status'                => ['sometimes', 'string'],
            'number'                => ['sometimes', 'string'],
            'total_net'             => ['sometimes', 'numeric'],
            'total_tax'             => ['sometimes', 'numeric'],
            'total_gross'           => ['sometimes', 'numeric'],
            'currency'              => ['sometimes', 'string', 'size:3'],
            'exchange_rate'         => ['sometimes', 'numeric'],
            'seller'                => ['sometimes', 'array'],
            'buyer'                 => ['sometimes', 'array'],
            'body'                  => ['sometimes', 'array'],
            'payment'               => ['sometimes', 'array'],
            'options'               => ['sometimes', 'array'],
        ];
    }
}
