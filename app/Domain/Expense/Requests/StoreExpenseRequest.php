<?php

namespace App\Domain\Expense\Requests;

use App\Domain\Financial\Enums\InvoiceType;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreExpenseRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'                  => ['required', new Enum(InvoiceType::class)],
            'issueDate'             => ['required', 'date'],
            'status'                => ['required', 'string'],
            'number'                => ['required', 'string'],
            'totalNet'              => ['required', 'numeric'],
            'totalTax'              => ['required', 'numeric'],
            'totalGross'            => ['required', 'numeric'],
            'currency'              => ['required', 'string', 'size:3'],
            'exchangeRate'          => ['required', 'numeric'],
            'seller'                => ['required', 'array'],
            'buyer'                 => ['required', 'array'],
            'body'                  => ['required', 'array'],
            'payment'               => ['required', 'array'],
            'options'               => ['required', 'array'],
        ];
    }
}
