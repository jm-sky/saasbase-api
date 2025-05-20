<?php

namespace App\Domain\Common\Requests;

use App\Http\Requests\BaseFormRequest;

class TagRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tag'    => ['sometimes', 'required', 'string', 'max:64'],
            'tags'   => ['sometimes', 'required', 'array'],
            'tags.*' => ['string', 'max:64'],
        ];
    }
}
