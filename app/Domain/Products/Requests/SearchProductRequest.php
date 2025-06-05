<?php

namespace App\Domain\Products\Requests;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Support\Str;

class SearchProductRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'filter.name'           => ['sometimes'],
            'filter.description'    => ['sometimes'],
            'filter.unitId'         => ['sometimes', 'ulid', 'exists:measurement_units,id'],
            'filter.vatRateId'      => ['sometimes', 'ulid', 'exists:vat_rates,id'],
            'filter.createdAt'      => ['sometimes'],
            'filter.createdAt.from' => ['sometimes', 'date'],
            'filter.createdAt.to'   => ['sometimes', 'date'],
            'filter.updatedAt'      => ['sometimes'],
            'filter.updatedAt.from' => ['sometimes', 'date'],
            'filter.updatedAt.to'   => ['sometimes', 'date'],
            'sort'                  => ['sometimes', 'string', 'in:name,-name,createdAt,-createdAt,updatedAt,-updatedAt'],
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
