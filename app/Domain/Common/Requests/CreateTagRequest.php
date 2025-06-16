<?php

namespace App\Domain\Common\Requests;

use App\Domain\Common\Enums\TagColor;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class CreateTagRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:64', 'unique:tags,name'],
            'color'   => ['nullable', 'sometimes', 'string', 'max:64', Rule::enum(TagColor::class)],
        ];
    }
}
