<?php

namespace App\Domain\Expense\Requests;

use App\Domain\Expense\Enums\AllocationDimensionType;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateDimensionConfigurationRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'configurations'                 => ['required', 'array'],
            'configurations.*.dimensionType' => [
                'required',
                'string',
                Rule::in(array_column(AllocationDimensionType::cases(), 'value')),
            ],
            'configurations.*.isEnabled'    => ['required', 'boolean'],
            'configurations.*.displayOrder' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'configurations.required'                 => 'Configuration data is required.',
            'configurations.*.dimensionType.required' => 'Dimension type is required for each configuration.',
            'configurations.*.dimensionType.in'       => 'Invalid dimension type.',
            'configurations.*.isEnabled.required'     => 'Enabled status is required for each configuration.',
            'configurations.*.isEnabled.boolean'      => 'Enabled status must be true or false.',
            'configurations.*.displayOrder.integer'   => 'Display order must be an integer.',
            'configurations.*.displayOrder.min'       => 'Display order must be 0 or greater.',
        ];
    }
}
