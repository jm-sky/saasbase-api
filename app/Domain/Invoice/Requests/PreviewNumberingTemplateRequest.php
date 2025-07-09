<?php

namespace App\Domain\Invoice\Requests;

use App\Domain\Invoice\Enums\ResetPeriod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PreviewNumberingTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'format'      => ['required', 'string'],
            'nextNumber'  => ['required', 'integer'],
            'resetPeriod' => ['required', 'string', Rule::in(ResetPeriod::values())],
            'prefix'      => ['nullable', 'string'],
            'suffix'      => ['nullable', 'string'],
        ];
    }
}
