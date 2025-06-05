<?php

namespace App\Domain\Ai\Requests;

use App\Http\Requests\BaseFormRequest;

class AiChatRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message'    => ['required', 'string'],
            'history'    => ['nullable', 'array'],
            'history.*'  => ['required', 'array'],
            'threadId'   => ['nullable', 'string'],
            'tempId'     => ['nullable', 'string'],
            'noHistory'  => ['nullable', 'boolean'],
        ];
    }
}
