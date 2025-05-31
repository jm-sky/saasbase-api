<?php

namespace App\Domain\Feeds\Requests;

use App\Http\Requests\BaseFormRequest;

class SearchFeedRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'perPage' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort'    => ['sometimes', 'string', 'in:createdAt,updatedAt,-createdAt,-updatedAt'],
        ];
    }
}
