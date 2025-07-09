<?php

namespace App\Domain\Invoice\Requests;

use App\Domain\Financial\Enums\InvoiceType;
use App\Domain\Invoice\Enums\ResetPeriod;
use App\Domain\Invoice\Rules\ValidNumberingFormatRule;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class StoreNumberingTemplateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string'],
            'invoiceType' => ['required', 'string', Rule::in(InvoiceType::values())],
            'format'      => ['required', 'string', new ValidNumberingFormatRule()],
            'nextNumber'  => ['required', 'integer'],
            'resetPeriod' => ['required', 'string', Rule::in(ResetPeriod::values())],
            'prefix'      => ['nullable', 'string'],
            'suffix'      => ['nullable', 'string'],
        ];
    }
}
