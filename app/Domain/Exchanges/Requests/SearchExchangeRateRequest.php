<?php

namespace App\Domain\Exchanges\Requests;

use App\Http\Requests\BaseFormRequest;

class SearchExchangeRateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['sometimes', 'date', 'date_format:Y-m-d'],
        ];
    }
}
