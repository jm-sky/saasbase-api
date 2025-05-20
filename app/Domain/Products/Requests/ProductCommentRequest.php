<?php

namespace App\Domain\Products\Requests;

use App\Http\Requests\BaseFormRequest;

class ProductCommentRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'The comment content is required.',
            'content.string'   => 'The comment content must be a string.',
            'content.max'      => 'The comment content may not be greater than :max characters.',
        ];
    }
}
