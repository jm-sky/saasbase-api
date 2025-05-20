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
            'filter.name'           => 'sometimes|string',
            'filter.email'          => 'sometimes|string',
            'filter.phone'          => 'sometimes|string',
            'filter.address'        => 'sometimes|string',
            'filter.city'           => 'sometimes|string',
            'filter.state'          => 'sometimes|string',
            'filter.zipCode'        => 'sometimes|string',
            'filter.country'        => 'sometimes|string',
            'filter.taxId'          => 'sometimes|string',
            'filter.notes'          => 'sometimes|string',
            'filter.isActive'       => 'sometimes|boolean',
            'filter.createdAt'      => 'sometimes|array',
            'filter.createdAt.from' => 'required_with:filter.createdAt|date',
            'filter.createdAt.to'   => 'required_with:filter.createdAt|date|after_or_equal:filter.createdAt.from',
            'filter.updatedAt'      => 'sometimes|array',
            'filter.updatedAt.from' => 'required_with:filter.updatedAt|date',
            'filter.updatedAt.to'   => 'required_with:filter.updatedAt|date|after_or_equal:filter.updatedAt.from',
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
