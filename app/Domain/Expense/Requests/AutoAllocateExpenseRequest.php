<?php

namespace App\Domain\Expense\Requests;

use App\Domain\Expense\Enums\AllocationDimensionType;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class AutoAllocateExpenseRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dimensions'        => ['nullable', 'array'],
            'dimensions.*.type' => [
                'required_with:dimensions',
                'string',
                Rule::in(array_column(AllocationDimensionType::cases(), 'value')),
            ],
            'dimensions.*.id' => ['required_with:dimensions', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'dimensions.*.type.required_with' => 'Dimension type is required when dimensions are provided.',
            'dimensions.*.type.in'            => 'Invalid dimension type.',
            'dimensions.*.id.required_with'   => 'Dimension ID is required when dimensions are provided.',
        ];
    }
}
