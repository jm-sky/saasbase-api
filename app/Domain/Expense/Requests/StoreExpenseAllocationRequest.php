<?php

namespace App\Domain\Expense\Requests;

use App\Domain\Expense\Enums\AllocationDimensionType;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseAllocationRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'allocations'                     => ['required', 'array', 'min:1'],
            'allocations.*.amount'            => ['required', 'numeric', 'min:0.01'],
            'allocations.*.note'              => ['nullable', 'string', 'max:1000'],
            'allocations.*.dimensions'        => ['nullable', 'array'],
            'allocations.*.dimensions.*.type' => [
                'required_with:allocations.*.dimensions',
                'string',
                Rule::in(array_column(AllocationDimensionType::cases(), 'value')),
            ],
            'allocations.*.dimensions.*.id' => ['required_with:allocations.*.dimensions', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'allocations.required'                          => 'At least one allocation is required.',
            'allocations.*.amount.required'                 => 'Allocation amount is required.',
            'allocations.*.amount.numeric'                  => 'Allocation amount must be a number.',
            'allocations.*.amount.min'                      => 'Allocation amount must be greater than 0.',
            'allocations.*.note.max'                        => 'Allocation note cannot exceed 1000 characters.',
            'allocations.*.dimensions.*.type.required_with' => 'Dimension type is required when dimensions are provided.',
            'allocations.*.dimensions.*.type.in'            => 'Invalid dimension type.',
            'allocations.*.dimensions.*.id.required_with'   => 'Dimension ID is required when dimensions are provided.',
        ];
    }
}
