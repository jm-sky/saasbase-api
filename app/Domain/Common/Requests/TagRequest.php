<?php

namespace App\Domain\Common\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TagRequest extends FormRequest
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
