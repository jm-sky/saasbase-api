<?php

namespace App\Domain\Products\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'filter.name' => ['sometimes', 'string'],
            'filter.description' => ['sometimes', 'string'],
            'filter.unitId' => ['sometimes', 'string', 'exists:units,id'],
            'filter.vatRateId' => ['sometimes', 'string', 'exists:vat_rates,id'],
            'filter.createdAt' => ['sometimes', 'string', 'regex:/^\d{4}-\d{2}-\d{2}(,\d{4}-\d{2}-\d{2})?$/'],
            'filter.updatedAt' => ['sometimes', 'string', 'regex:/^\d{4}-\d{2}-\d{2}(,\d{4}-\d{2}-\d{2})?$/'],
            'sort' => ['sometimes', 'string', 'in:name,-name,createdAt,-createdAt,updatedAt,-updatedAt'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Map camelCase filter keys to snake_case for the backend
        if ($this->has('filter')) {
            $filter = collect($this->filter)->mapWithKeys(function ($value, $key) {
                $snakeKey = match ($key) {
                    'unitId' => 'unit_id',
                    'vatRateId' => 'vat_rate_id',
                    'createdAt' => 'created_at',
                    'updatedAt' => 'updated_at',
                    default => $key,
                };
                return [$snakeKey => $value];
            })->all();

            $this->merge(['filter' => $filter]);
        }

        // Map camelCase sort to snake_case
        if ($this->has('sort')) {
            $sort = str_replace(['createdAt', 'updatedAt'], ['created_at', 'updated_at'], $this->sort);
            $this->merge(['sort' => $sort]);
        }
    }
}
