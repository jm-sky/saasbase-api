<?php

namespace App\Domain\Financial\Requests;

use App\Domain\Common\Rules\ValidAdvancedFilterRule;
use App\Http\Requests\BaseFormRequest;

class SearchPKWiUClassificationRequest extends BaseFormRequest
{
    private array $allowedSortColumns = [
        'name',
        'createdAt',
        'updatedAt',
        // Add your allowed sort columns here
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $sortValidationString = $this->generateSortValidationString();

        return [
            'filter.id'         => ['sometimes', new ValidAdvancedFilterRule('string')],
            'filter.name'       => ['sometimes', new ValidAdvancedFilterRule('string')], // Update with actual fields
            'filter.createdAt'  => ['sometimes', new ValidAdvancedFilterRule('date')],
            'filter.updatedAt'  => ['sometimes', new ValidAdvancedFilterRule('date')],
            'sort'              => ['sometimes', 'string', 'in:' . $sortValidationString],
        ];
    }

    private function generateSortValidationString(): string
    {
        $sortOptions = [];

        foreach ($this->allowedSortColumns as $column) {
            $sortOptions[] = $column;
            $sortOptions[] = '-' . $column;
        }

        return implode(',', $sortOptions);
    }
}
