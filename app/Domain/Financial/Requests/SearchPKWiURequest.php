<?php

namespace App\Domain\Financial\Requests;

use App\Http\Requests\BaseFormRequest;

class SearchPKWiURequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'query' => ['required', 'string', 'min:2', 'max:255'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function getQuery(): string
    {
        return $this->input('query');
    }
}
