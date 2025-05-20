<?php

namespace App\Domain\Common\Requests;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Support\Str;

class SearchCountryRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'filter.name'           => 'sometimes|string',
            'filter.code'           => 'sometimes|string|size:2',
            'filter.code3'          => 'sometimes|string|size:3',
            'filter.numericCode'    => 'sometimes|string',
            'filter.phoneCode'      => 'sometimes|string',
            'filter.capital'        => 'sometimes|string',
            'filter.currency'       => 'sometimes|string',
            'filter.currencyCode'   => 'sometimes|string',
            'filter.currencySymbol' => 'sometimes|string',
            'filter.tld'            => 'sometimes|string',
            'filter.native'         => 'sometimes|string',
            'filter.region'         => 'sometimes|string',
            'filter.subregion'      => 'sometimes|string',
            'filter.emoji'          => 'sometimes|string',
            'filter.emojiU'         => 'sometimes|string',
            'filter.createdAt'      => 'sometimes|array',
            'filter.createdAt.from' => 'required_with:filter.createdAt|date',
            'filter.createdAt.to'   => 'required_with:filter.createdAt|date|after_or_equal:filter.createdAt.from',
            'filter.updatedAt'      => 'sometimes|array',
            'filter.updatedAt.from' => 'required_with:filter.updatedAt|date',
            'filter.updatedAt.to'   => 'required_with:filter.updatedAt|date|after_or_equal:filter.updatedAt.from',
            'sort'                  => ['sometimes', 'string', 'in:name,-name,code,-code,code3,-code3,numericCode,-numericCode,phoneCode,-phoneCode,capital,-capital,currency,-currency,currencyCode,-currencyCode,region,-region,subregion,-subregion,createdAt,-createdAt,updatedAt,-updatedAt'],
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
