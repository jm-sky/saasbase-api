<?php

namespace App\Domain\Contractors\Requests;

use App\Domain\Common\Rules\ValidAdvancedFilterRule;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Support\Str;

class SearchContractorRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'filter.name'           => ['sometimes', new ValidAdvancedFilterRule('string')],
            'filter.email'          => ['sometimes', new ValidAdvancedFilterRule('string')],
            'filter.phone'          => ['sometimes', new ValidAdvancedFilterRule('string')],
            'filter.address'        => ['sometimes', new ValidAdvancedFilterRule('string')],
            'filter.city'           => ['sometimes', new ValidAdvancedFilterRule('string')],
            'filter.state'          => ['sometimes', new ValidAdvancedFilterRule('string')],
            'filter.zipCode'        => ['sometimes', new ValidAdvancedFilterRule('string')],
            'filter.country'        => ['sometimes', new ValidAdvancedFilterRule('string')],
            'filter.taxId'          => ['sometimes', new ValidAdvancedFilterRule('string')],
            'filter.notes'          => ['sometimes', new ValidAdvancedFilterRule('string')],
            'filter.isActive'       => ['sometimes', new ValidAdvancedFilterRule('boolean')],
            'filter.createdAt'      => ['sometimes', new ValidAdvancedFilterRule('date')],
            'filter.updatedAt'      => ['sometimes', new ValidAdvancedFilterRule('date')],
            'sort'                  => ['sometimes', 'string', 'in:name,-name,email,-email,city,-city,country,-country,isActive,-isActive,createdAt,-createdAt,updatedAt,-updatedAt'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Transform snake_case filter keys to camelCase
        if ($this->has('filter')) {
            $filter = collect($this->filter)->mapWithKeys(function ($value, $key) {
                return [Str::camel($key) => $value];
            })->toArray();

            $this->merge(['filter' => $filter]);
        }

        // Transform sort parameter
        if ($this->has('sort')) {
            $sort      = $this->input('sort');
            $direction = str_starts_with($sort, '-') ? '-' : '';
            $field     = ltrim($sort, '-');

            $this->merge([
                'sort' => $direction . Str::camel($field),
            ]);
        }
    }
}
