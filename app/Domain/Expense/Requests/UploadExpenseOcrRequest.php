<?php

namespace App\Domain\Expense\Requests;

use App\Http\Requests\BaseFormRequest;

class UploadExpenseOcrRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'files'   => ['required', 'array', 'min:1'],
            'files.*' => ['required', 'file'],
            'month'   => ['nullable', 'sometimes', 'integer', 'min:1', 'max:12'],
            'year'    => ['nullable', 'sometimes', 'integer', 'min:2000'],
        ];
    }
}
