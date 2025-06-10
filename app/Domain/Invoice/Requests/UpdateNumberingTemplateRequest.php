<?php

namespace App\Domain\Invoice\Requests;

use App\Domain\Invoice\Enums\InvoiceType;
use App\Domain\Invoice\Enums\ResetPeriod;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateNumberingTemplateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => ['sometimes', 'string', 'max:255'],
            'invoiceType'  => ['sometimes', new Enum(InvoiceType::class)],
            'format'       => ['sometimes', 'string', 'max:255'],
            'nextNumber'   => ['sometimes', 'integer', 'min:1'],
            'resetPeriod'  => ['sometimes', new Enum(ResetPeriod::class)],
            'prefix'       => ['sometimes', 'nullable', 'string', 'max:50'],
            'suffix'       => ['sometimes', 'nullable', 'string', 'max:50'],
            'isDefault'    => ['sometimes', 'boolean'],
        ];
    }
}
