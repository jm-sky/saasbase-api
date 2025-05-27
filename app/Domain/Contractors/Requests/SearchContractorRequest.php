<?php

namespace App\Domain\Contractors\Requests;

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
            'filter.name'           => 'sometimes',
            'filter.email'          => 'sometimes',
            'filter.phone'          => 'sometimes',
            'filter.address'        => 'sometimes',
            'filter.city'           => 'sometimes',
            'filter.state'          => 'sometimes',
            'filter.zipCode'        => 'sometimes',
            'filter.country'        => 'sometimes',
            'filter.taxId'          => 'sometimes',
            'filter.notes'          => 'sometimes',
            'filter.isActive'       => 'sometimes',
            'filter.createdAt'      => 'sometimes',
            'filter.createdAt.from' => 'sometimes|date',
            'filter.createdAt.to'   => 'sometimes|date',
            'filter.updatedAt'      => 'sometimes',
            'filter.updatedAt.from' => 'sometimes|date',
            'filter.updatedAt.to'   => 'sometimes|date',
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
